<?php

namespace Caiocesar173\Booking\Traits;

use Caiocesar173\Utils\Exceptions\ApiException;

use Caiocesar173\Booking\Entities\BookableFee;
use Caiocesar173\Booking\Entities\BookableBooking;
use Caiocesar173\Booking\Entities\BookableAvailability;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;

trait BookableTrait
{
    use BookingScopesTrait;

    /**
     * Register a deleted model event with the dispatcher.
     *
     * @param \Closure|string $callback
     *
     * @return void
     */
    abstract public static function deleted($callback);

    /**
     * Define a polymorphic one-to-many relationship.
     *
     * @param string $related
     * @param string $name
     * @param string $type
     * @param string $id
     * @param string $localKey
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphMany
     */
    abstract public function morphMany($related, $name, $type = null, $id = null, $localKey = null);

    /**
     * Boot the Bookable trait for the model.
     *
     * @return void
     */
    public static function bootBookableTrait()
    {
        static::deleted(function (self $model) {
            $model->bookings()->delete();
        });
    }

    /**
     * Attach the given bookings to the model.
     *
     * @param \Illuminate\Database\Eloquent\Collection|\Illuminate\Support\Collection|array $ids
     * @param mixed                                                                         $bookings
     *
     * @return void
     */
    public function setBookingsAttribute($bookings): void
    {
        static::saved(function (self $model) use ($bookings) {
            $this->bookings()->sync($bookings);
        });
    }

    /**
     * Attach the given rates to the model.
     *
     * @param \Illuminate\Database\Eloquent\Collection|\Illuminate\Support\Collection|array $ids
     * @param mixed                                                                         $rates
     *
     * @return void
     */
    public function setRatesAttribute($rates): void
    {
        static::saved(function (self $model) use ($rates) {
            $this->rates()->sync($rates);
        });
    }

    /**
     * Attach the given availabilities to the model.
     *
     * @param \Illuminate\Database\Eloquent\Collection|\Illuminate\Support\Collection|array $ids
     * @param mixed                                                                         $availabilities
     *
     * @return void
     */
    public function setAvailabilitiesAttribute($availabilities): void
    {
        static::saved(function (self $model) use ($availabilities) {
            $this->availabilities()->sync($availabilities);
        });

        static::created(function (self $model) use ($availabilities) {
            $this->availabilities()->sync($availabilities);
        });
    }

    /**
     * The resource may have many bookings.
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphMany
     */
    public function bookings(): MorphMany
    {
        return $this->morphMany(BookableBooking::class, 'bookable', 'bookable_type', 'bookable_id');
    }

    /**
     * Get bookings by the given customer.
     *
     * @param \Illuminate\Database\Eloquent\Model $customer
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphMany
     */
    public function bookingsBy(Model $customer): MorphMany
    {
        return $this->bookings()->where('customer_type', $customer->getMorphClass())->where('customer_id', $customer->getKey());
    }

    /**
     * The resource may have many availabilities.
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphMany
     */
    public function availabilities(): MorphMany
    {
        return $this->morphMany(BookableAvailability::class, 'bookable', 'bookable_type', 'bookable_id');
    }

    /**
     * The resource may have many rates.
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphMany
     */
    public function rates(): MorphMany
    {
        return $this->morphMany(BookableFee::class, 'bookable', 'bookable_type', 'bookable_id');
    }

    /**
     * Book the model for the given customer at the given dates with the given price.
     *
     * @param \Illuminate\Database\Eloquent\Model $customer
     * @param string                              $startsAt
     * @param string                              $endsAt
     *
     * @return \Caiocesar173\Booking\Entities\BookableBooking
     */
    public function newBooking(Model $customer, string $startsAt, string $endsAt): BookableBooking
    {
        $data = [
            'starts_at' => (new Carbon($startsAt))->toDateTimeString(),
            'ends_at' => (new Carbon($endsAt))->toDateTimeString(),
        ];
        
        $booking = $this->bookings()->make($data)->customer()->associate($customer);

        if ($booking->save()) return $booking;

        throw new ApiException('Não foi possivel realizar o agendamento', 400);
    }
}
