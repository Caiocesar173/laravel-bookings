<?php

namespace Caiocesar173\Booking\Entities;

use Caiocesar173\Utils\Traits\ValidatingTrait;
use Caiocesar173\Utils\Abstracts\ModelAbstract;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

abstract class BookableAvailability extends ModelAbstract 
{
    use HasFactory;
    use ValidatingTrait;

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
        'status'
    ];

    /**
     * {@inheritdoc}
     */
    protected $casts = [
        'bookable_id' => 'string',
        'bookable_type' => 'string',
        'range' => 'string',
        'from' => 'string',
        'to' => 'string',
        'is_bookable' => 'boolean',
        'priority' => 'integer',
    ];

    /**
     * {@inheritdoc}
     */
    protected $observables = [
        'validating',
        'validated',
    ];

    /**
     * The default rules that the model will validate against.
     *
     * @var array
     */
    protected $rules = [];

    /**
     * Whether the model should throw a
     * ValidationException if it fails validation.
     *
     * @var bool
     */
    protected $throwValidationExceptions = true;

    /**
     * Create a new Eloquent model instance.
     *
     * @param array $attributes
     */
    public function __construct(array $attributes = [])
    {
        $this->mergeRules([
            'bookable_id' => 'required|integer',
            'bookable_type' => 'required|string|strip_tags|max:150',
            'range' => 'required|in:datetimes,dates,months,weeks,days,times,sunday,monday,tuesday,wednesday,thursday,friday,saturday',
            'from' => 'required|string|strip_tags|max:150',
            'to' => 'required|string|strip_tags|max:150',
            'is_bookable' => 'required|boolean',
            'priority' => 'nullable|integer',
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
}

