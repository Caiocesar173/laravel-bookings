<?php

namespace Caiocesar173\Booking\Entities;

use Caiocesar173\Utils\Rules\UuidRule;
use Caiocesar173\Utils\Enum\StatusEnum;
use Caiocesar173\Utils\Entities\Currency;
use Caiocesar173\Utils\Traits\HasSlugTrait;
use Caiocesar173\Utils\Abstracts\ModelAbstract;
use Caiocesar173\Utils\Exceptions\ArrayException;
use Caiocesar173\Utils\Exceptions\ValidatorException;
use Caiocesar173\Utils\Exceptions\DefaultCurrencyException;

use Caiocesar173\Booking\Enum\TimeUnitEnum;
use Caiocesar173\Booking\Enum\DatesRangeEnum;
use Caiocesar173\Booking\Traits\BookableTrait;
use Caiocesar173\Utils\Database\Factories\BookableFactory;

use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\Factory;

use Spatie\Sluggable\SlugOptions;
use Spatie\EloquentSortable\Sortable;
use Spatie\EloquentSortable\SortableTrait;

class Bookable extends ModelAbstract implements Sortable
{
    use HasSlugTrait;
    use BookableTrait;
    use SortableTrait;

    protected $neededColumns = [
        'slug',
        'name',
        'description',
        'base_cost',
        'unit_cost',
        'currency',
        'unit',
        'maximum_units',
        'minimum_units',
        'is_cancelable',
        'is_recurring',
        'sort_order',
        'capacity',
        'style',
        'status',
    ];

    /**
     * {@inheritdoc}
     */
    protected $fillable = [];

    /**
     * {@inheritdoc}
     */
    protected $casts = [
        'slug'          => 'string',
        'name'          => 'string',
        'description'   => 'string',
        'base_cost'     => 'float',
        'unit_cost'     => 'float',
        'currency'      => 'string',
        'unit'          => 'string',
        'maximum_units' => 'integer',
        'minimum_units' => 'integer',
        'is_cancelable' => 'boolean',
        'is_recurring'  => 'boolean',
        'sort_order'    => 'integer',
        'capacity'      => 'integer',
        'style'         => 'string',
        'deleted_at'    => 'datetime:Y-m-d H:00:00',
    ];

    /**
     * {@inheritdoc}
     */
    public $sortable = [
        'order_column_name' => 'sort_order',
    ];

    /**
     * Create a new Eloquent model instance.
     *
     * @param array $attributes
     */
    public function __construct(array $attributes = [])
    {
        $this->verifyColumns();

        $this->fillable += $this->neededColumns;
        $this->mergeRules([
            'slug'          => ['nullable', 'alpha_dash', 'max:150'],
            'name'          => ['required', 'string', 'max:150'],
            'description'   => ['nullable', 'string', 'max:32768'],
            'base_cost'     => ['nullable', 'numeric'],
            'unit_cost'     => ['nullable', 'numeric'],
            'currency'      => ['nullable', new UuidRule],
            'unit'          => ['nullable', Rule::in(TimeUnitEnum::keys())],
            'maximum_units' => ['nullable', 'integer', 'max:100000'],
            'minimum_units' => ['nullable', 'integer', 'max:100000'],
            'is_cancelable' => ['nullable', 'boolean'],
            'is_recurring'  => ['nullable', 'boolean'],
            'sort_order'    => ['nullable', 'integer', 'max:100000'],
            'capacity'      => ['nullable', 'integer', 'max:100000'],
            'style'         => ['nullable', 'string', 'max:150'],
            'status'        => ['nullable', Rule::in(StatusEnum::keys())],
        ]);

        parent::__construct($attributes);
    }

    public function create($data)
    {
        if (!isset($data['currency'])) {
            $defaultCorrency = app(Currency::class)->getDefaultCurrency();

            if (!$defaultCorrency)
                throw new DefaultCurrencyException();

            $data['currency'] = $defaultCorrency->id;
        }

        $errorMessages = $this->validate($data);
        if (!is_null($errorMessages))
            throw new ValidatorException("Não foi possivel criar $this->entityName", 400, $errorMessages);

        DB::beginTransaction();
        try {
            $model = parent::make($data);
            if ($model->save()) {
                /**
                 * Creating BookableAvailability
                 */
                $availability = [
                    'bookable_id'   => $model->id,
                    'bookable_type' => get_class($model),
                    'from'          => isset($data['from'])     ? $data['from']     : null,
                    'to'            => isset($data['to'])       ? $data['to']       : null,
                    'priority'      => isset($data['priority']) ? $data['priority'] : 1,
                ];

                if (isset($data['range'])) {
                    $dateRange = DatesRangeEnum::getValue($data['range']);
                    if (!empty($dateRange))
                        $availability['range'] = $dateRange;
                }

                $availability = BookableAvailability::make($availability);
                if ($availability->save()) {
                    /**
                     * Creating BookableFee
                     */
                    $bookableRate = [
                        'range'     => !is_null($availability->range)     ? $availability->range                       : null,
                        'from'      => !is_null($availability->from)      ? $availability->from->format('Y-m-d h:i:s') : null,
                        'to'        => !is_null($availability->to)        ? $availability->to->format('Y-m-d h:i:s')   : null,
                        'priority'  => $availability->priority,
                        'base_cost' => !is_null($availability->base_cost) ? $model->base_cost                          : 0.00,
                        'unit_cost' => !is_null($availability->unit_cost) ? $model->unit_cost                          : 0.00,
                    ];

                    $bookableRate = BookableFee::make($bookableRate)->bookable()->associate($model);
                    if ($bookableRate->save())
                        DB::commit();

                    $model->range = !is_null($availability->range) ? $availability->range : null;
                    $model->from = !is_null($availability->from) ? $availability->from->format('Y-m-d h:i:s') : null;
                    $model->to = !is_null($availability->to) ? $availability->to->format('Y-m-d h:i:s') : null;
                    $model->currency = app(Currency::class)->find($model->currency)->code;

                    return $model;
                }
            }
            DB::rollback();
            throw new ArrayException($errorMessages);
        } catch (\Exception $error) {
            DB::rollback();

            $error = [$error->getMessage()];
            if ($error == "")
                $error =  $errorMessages;

            if($this->throwValidationException)
                throw new ValidatorException("Não foi possivel criar $this->entityName", 400, $error);
            else
                return $error; 
        }
    }

    public function exclude()
    {
        $availabilities = app(BookableAvailability::class)->bookable()->associate($this->id)->get();
        foreach ($availabilities as $availability) {
            $availability->exclude();
        }

        $rates = app(BookableFee::class)->bookable()->associate($this->id)->get();
        foreach ($rates as $rate) {
            $rate->exclude();
        }

        return parent::exclude();
    }

    public function currency()
    {
        return app(Currency::class)->find($this->currency);
    }

    public function getCurrencyAtrribute()
    {
        return app(Currency::class)->find($this->currency)->code;
    }

    protected function verifyColumns()
    {
        $modelColumns = Schema::getColumnListing($this->getTable());
        $duplicateColumns = array_intersect($this->neededColumns, $modelColumns);

        if (count($duplicateColumns) == count($this->neededColumns))
            return;

        foreach ($duplicateColumns as $column) {
            if (Schema::hasColumn($this->getTable(), $column))
                Schema::table($this->getTable(), function (Blueprint $table) use ($column) {
                    $table->dropColumn($column);
                });
        }

        $this->createColumns();
    }

    protected function createColumns()
    {
        Schema::table($this->getTable(), function (Blueprint $table) {
            $table->string('slug', '150')->after('id')->default("");
            $table->string('name', '150')->after('slug');
            $table->text('description')->after('name');
            $table->decimal('base_cost', 11, 2)->default(0.00)->after('description');
            $table->decimal('unit_cost', 11, 2)->default(0.00)->after('base_cost');
            $table->foreignUuid('currency')->references('id')->on('currency')->after('base_cost');
            $table->enum('unit', TimeUnitEnum::keys())->default(TimeUnitEnum::DAY)->after('currency');
            $table->bigInteger('maximum_units')->default(1)->nullable()->after('currency');
            $table->bigInteger('minimum_units')->default(1)->nullable()->after('maximum_units');
            $table->boolean('is_cancelable')->default(true)->after('minimum_units');
            $table->boolean('is_recurring')->default(false)->after('is_cancelable');
            $table->bigInteger('sort_order')->nullable()->after('is_recurring');
            $table->bigInteger('capacity')->default(1)->after('sort_order');
            $table->string('style', '150')->nullable()->after('capacity');
            $table->enum('status', StatusEnum::keys())->default(StatusEnum::ACTIVE)->after('capacity');
        });
    }

    /** 
     * Get the active resources.
     *
     * @param \Illuminate\Database\Eloquent\Builder $builder
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeActive(Builder $builder): Builder
    {
        return $builder->where('status', StatusEnum::ACTIVE);
    }

    /**
     * Get the inactive resources.
     *
     * @param \Illuminate\Database\Eloquent\Builder $builder
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeInactive(Builder $builder): Builder
    {
        return $builder->where('status', StatusEnum::INACTIVE);
    }

    /**
     * Get the options for generating the slug.
     *
     * @return \Spatie\Sluggable\SlugOptions
     */
    public function getSlugOptions(): SlugOptions
    {
        return SlugOptions::create()
            ->doNotGenerateSlugsOnUpdate()
            ->generateSlugsFrom('name')
            ->saveSlugsTo('slug');
    }

    /**
     * Activate the resource.
     *
     * @return $this
     */
    public function activate()
    {
        return parent::edit(['status' => StatusEnum::ACTIVE], $this);
    }

    /**
     * Deactivate the resource.
     *
     * @return $this
     */
    public function deactivate()
    {
        return parent::edit(['status' => StatusEnum::INACTIVE], $this);
    }

    protected static function newFactory(): Factory
    {
        return BookableFactory::new();
    }
}
