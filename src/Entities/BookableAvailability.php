<?php

namespace Caiocesar173\Booking\Entities;

use Caiocesar173\Utils\Rules\UuidRule;
use Caiocesar173\Utils\Enum\StatusEnum;
use Caiocesar173\Booking\Enum\DatesRangeEnum;
use Caiocesar173\Utils\Abstracts\ModelAbstract;
use Caiocesar173\Utils\Database\Factories\BookableAvailabilityFactory;

use Illuminate\Validation\Rule;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class BookableAvailability extends ModelAbstract
{
    protected $table = 'bookable_availabilities';
    protected $primaryKey = 'id';

    /**
     * {@inheritdoc}
     */
    protected $fillable = [
        'bookable_id',
        'bookable_type',
        'range',
        'from',
        'to',
        'is_bookable',
        'priority',
        'status',
    ];

    /**
     * {@inheritdoc}
     */
    protected $casts = [
        'bookable_id'   => 'string',
        'bookable_type' => 'string',
        'range'         => 'string',
        'from'          => 'datetime:Y-m-d h:i:s',
        'to'            => 'datetime:Y-m-d h:i:s',
        'is_bookable'   => 'boolean',
        'priority'      => 'integer',
        'status'        => 'string',
    ];

    /**
     * Create a new Eloquent model instance.
     *
     * @param array $attributes
     */
    public function __construct(array $attributes = [])
    {
        $this->mergeRules([
            'bookable_id'   => ['required', new UuidRule],
            'bookable_type' => ['required', 'string'],
            'from'          => ['nullable', 'date:Y-m-d h:i:s'],
            'to'            => ['nullable', 'date:Y-m-d h:i:s'],
            'is_bookable'   => ['nullable', 'boolean'],
            'priority'      => ['nullable', 'integer'],
            'range'         => ['nullable',  Rule::in(DatesRangeEnum::keys())],
            'status'        => ['nullable', Rule::in(StatusEnum::keys())],
        ]);


        parent::__construct($attributes);
    }

    /**
     * Get the owning resource model.
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphTo
     */
    public function bookable(): MorphTo
    {
        return $this->morphTo('bookable', 'bookable_type', 'bookable_id', 'id');
    }

    protected static function newFactory(): Factory
    {
        return BookableAvailabilityFactory::new();
    }
}
