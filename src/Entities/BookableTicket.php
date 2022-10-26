<?php

namespace Caiocesar173\Booking\Entities;

use Caiocesar173\Utils\Rules\UuidRule;
use Caiocesar173\Utils\Enum\StatusEnum;
use Caiocesar173\Utils\Entities\Currency;
use Caiocesar173\Utils\Traits\HasSlugTrait;
use Caiocesar173\Utils\Abstracts\ModelAbstract;
use Caiocesar173\Booking\Traits\TicketableTrait;
use Caiocesar173\Utils\Database\Factories\BookableTicketFactory;
use Spatie\Sluggable\SlugOptions;
use Spatie\EloquentSortable\SortableTrait;

use Illuminate\Validation\Rule;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class BookableTicket extends ModelAbstract
{
    use HasSlugTrait;
    use SortableTrait;
    use TicketableTrait;

    protected $table = 'bookable_tickets';
    protected $primaryKey = 'id';

    /**
     * {@inheritdoc}
     */
    protected $fillable = [
        'slug',
        'booking',
        'responsable_id',
        'responsable_type',
        'price',
        'currency',
        'quantity',
        'sort_order',
        'is_paied',
        'is_approved',
        'is_confirmed',
        'is_attended',
        'notes',
    ];

    /**
     * {@inheritdoc}
     */
    protected $casts = [
        'slug'             => 'string',
        'booking'          => 'string',
        'responsable_id'   => 'string',
        'responsable_type' => 'string',
        'price'            => 'float', 
        'currency'         => 'string',
        'quantity'         => 'integer',
        'sort_order'       => 'integer',
        'is_paied'         => 'boolean',
        'is_approved'      => 'boolean',
        'is_confirmed'     => 'boolean',
        'is_attended'      => 'boolean',
        'notes'            => 'string',
        'status'           => 'string',
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
        $this->mergeRules([
            'slug'             => ['required', 'alpha_dash', 'max:150'],
            'booking'          => ['required', new UuidRule],
            'responsable_id'   => ['required', new UuidRule],
            'responsable_type' => ['required', 'string'],
            'price'            => ['nullable', 'numeric'],
            'currency'         => ['nullable', new UuidRule],
            'quantity'         => ['nullable', 'integer', 'max:100000'],
            'sort_order'       => ['nullable', 'integer', 'max:100000'],
            'is_paied'         => ['nullable', 'boolean'],
            'is_approved'      => ['nullable', 'boolean'],
            'is_confirmed'     => ['nullable', 'boolean'],
            'is_attended'      => ['nullable', 'boolean'],
            'notes'            => ['nullable', 'string', 'max:32768'],
            'status'           => ['nullable', Rule::in(StatusEnum::keys())]
        ]);

        $this->__construct($attributes);
    }

    public function create($data)
    {   
        $data['currency'] = Currency::verifyCurrency($data['currency']);

        return $this->create($data);
    }

    public function currency()
    {
        return $this->hasOne(Currency::class, 'currency', 'id');
    }

    public function booking()
    {
        return $this->belongsTo(BookableBooking::class, 'booking', 'id');
    }

    public function getCurrencyAttribute()
    {
        $currency = Currency::find($this->currency);
        return $currency->code;
    }

    /**
     * Get the booking responsable.
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphTo
     */
    public function responsable(): MorphTo
    {
        return $this->morphTo('responsable', 'responsable_type', 'responsable_id', 'id');
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
     * Confirm the reservation an then send to be paied
     *
     * @return boolean
     */
    public function confirm()
    {
        /**
         * TODO
         * 
         * Confirm the reservation
         * Send to be paied
         */

        return $this->edit(['is_confirmed' => true]);
    }

    /**
     * Confirm the reservation as payed
     *
     * @return boolean
     */
    public function pay()
    {
        /**
         * TODO
         * 
         * Check if is confirmed
         */

        return $this->edit(['is_paied' => true]);
    }


    /**
     * Checks if the booking is aprovable an then marks as aproved 
     *
     * @return boolean
     */
    public function approve()
    {
        /**
         * TODO
         * 
         * Check if is paied
         */

        return $this->edit(['is_approved' => true]);
    }

     /**
     * Checks if the booking is aprovable an then marks as aproved 
     *
     * @return boolean
     */
    public function attend()
    {
        /**
         * TODO
         * 
         * Check if is approved
         */

        return $this->edit(['is_attended' => true]);
    }

    protected static function newFactory(): Factory
    {
        return BookableTicketFactory::new();
    }
}
