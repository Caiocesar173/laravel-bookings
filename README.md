<p align="center" id="project-title">
    <img width="200" src="https://cacodes.com.br/img/logo/logo.svg" align="center" alt="GitHub Readme Stats" />
</p>

# Laravel Bookings
---

This is a Laravel package which created to made to add booking functionality in your application.
This package is based uppon, [laravel-bookings](https://github.com/rinvex/laravel-bookings).

## TODO

- [ ] Add the ability to cancel bookings.
- [ ] Complete the bookable availability implementation, and document it.

## Considerations

- **Laravel Bookings** assumes that your resource model has at least three fields, `price` as a decimal field, and lastly `unit` as a string field which accepts one of (minute, hour, day, month) respectively.
- Payments and ordering are out of scope for **Laravel Bookings**, so you've to take care of this yourself. Booking price is calculated by this package, so you may need to hook into the process or listen to saved bookings to issue invoice, or trigger payment process.
- You may extend **Laravel Bookings** functionality to add features like: minimum and maximum units, and many more. These features may be supported natively sometime in the future.

## Installation

1. Install the package via composer:
    ```shell
    composer require caiocesar173/laravel-bookings
    ```

2. Execute migrations via the following command:
    ```shell
    php artisan migrate
    ```

3. Done!
## Usage
### Add bookable functionality to your resource model

To add bookable functionality to your resource model just use the `\Caiocesar173\Booking\Traits\BookableTrait` trait like this:

```php
namespace App\Models;

use Caiocesar173\Booking\Traits\BookableTrait;
use Illuminate\Database\Eloquent\Model;

class Room extends Model
{
    use BookableTrait;
}
```

That's it, you only have to use that trait in your Room model! Now your rooms will be bookable.

### Add bookable functionality to your customer model

To add bookable functionality to your customer model just use the `\Caiocesar173\Booking\Traits\HasBookingsTrait` trait like this:

```php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Caiocesar173\Booking\Traits\HasBookingsTrait;

class Customer extends Model
{
    use HasBookingsTrait;
}
```

Again, that's all you need to do! Now your Customer model can book resources.

### Create a new booking

Creating a new booking is straight forward, and could be done in many ways. Let's see how could we do that:

```php
$room = \App\Models\Room::find(1);
$customer = \App\Models\Customer::find(1);

// Extends \Caiocesar173\Booking\Models\BookableBooking
$serviceBooking = new \App\Models\ServiceBooking;

// Create a new booking via resource model (customer, starts, ends)
$room->newBooking($customer, '2017-07-05 12:44:12', '2017-07-10 18:30:11');

// Create a new booking via customer model (resource, starts, ends)
$customer->newBooking($room, '2017-07-05 12:44:12', '2017-07-10 18:30:11');

// Create a new booking explicitly
$serviceBooking->make(['starts_at' => \Carbon\Carbon::now(), 'ends_at' => \Carbon\Carbon::tomorrow()])
        ->customer()->associate($customer)
        ->bookable()->associate($room)
        ->save();
```

> **Notes:**
> - As you can see, there's many ways to create a new booking, use whatever suits your context.
> - Booking price is calculated automatically on the fly according to the resource price, custom prices, and bookable Rates.
> - **Laravel Bookings** is intelegent enough to detect date format and convert if required, the above example show the explicitly correct format, but you still can write something like: 'Tomorrow 1pm' and it will be converted automatically for you.

### Query booking models

You can get more details about a specific booking as follows:

```php
// Extends \Caiocesar173\Booking\Models\BookableBooking
$serviceBooking = \App\Models\ServiceBooking::find(1);

$bookable = $serviceBooking->bookable; // Get the owning resource model
$customer = $serviceBooking->customer; // Get the owning customer model

$serviceBooking->isPast(); // Check if the booking is past
$serviceBooking->isFuture(); // Check if the booking is future
$serviceBooking->isCurrent(); // Check if the booking is current
$serviceBooking->isCancelled(); // Check if the booking is cancelled
```

And as expected, you can query bookings by date as well:

```php
// Extends \Caiocesar173\Booking\Models\BookableBooking
$serviceBooking = new \App\Models\ServiceBooking;

$pastBookings = $serviceBooking->past(); // Get the past bookings
$futureBookings = $serviceBooking->future(); // Get the future bookings
$currentBookings = $serviceBooking->current(); // Get the current bookings
$cancelledBookings = $serviceBooking->cancelled(); // Get the cancelled bookings

$serviceBookingsAfter = $serviceBooking->startsAfter('2017-06-21 19:28:51')->get(); // Get bookings starts after the given date
$serviceBookingsStartsBefore = $serviceBooking->startsBefore('2017-06-21 19:28:51')->get(); // Get bookings starts before the given date
$serviceBookingsBetween = $serviceBooking->startsBetween('2017-06-21 19:28:51', '2017-07-01 12:00:00')->get(); // Get bookings starts between the given dates

$serviceBookingsEndsAfter = $serviceBooking->endsAfter('2017-06-21 19:28:51')->get(); // Get bookings starts after the given date
$serviceBookingsEndsBefore = $serviceBooking->endsBefore('2017-06-21 19:28:51')->get(); // Get bookings starts before the given date
$serviceBookingsEndsBetween = $serviceBooking->endsBetween('2017-06-21 19:28:51', '2017-07-01 12:00:00')->get(); // Get bookings starts between the given dates

$serviceBookingsCancelledAfter = $serviceBooking->cancelledAfter('2017-06-21 19:28:51')->get(); // Get bookings starts after the given date
$serviceBookingsCancelledBefore = $serviceBooking->cancelledBefore('2017-06-21 19:28:51')->get(); // Get bookings starts before the given date
$serviceBookingsCancelledBetween = $serviceBooking->cancelledBetween('2017-06-21 19:28:51', '2017-07-01 12:00:00')->get(); // Get bookings starts between the given dates

$room = \App\Models\Room::find(1);
$serviceBookingsOfBookable = $serviceBooking->ofBookable($room)->get(); // Get bookings of the given resource

$customer = \App\Models\Customer::find(1);
$serviceBookingsOfCustomer = $serviceBooking->ofCustomer($customer)->get(); // Get bookings of the given customer
```

### Create a new booking rate

Bookable Rates are special criteria used to modify the default booking price. For example, let’s assume that you have a resource charged per hour, and you need to set a higher price for the first "2" hours to cover certain costs, while discounting pricing if booked more than "5" hours. That’s totally achievable through bookable Rates. Simply set the amount of units to apply this criteria on, and state the percentage you’d like to have increased or decreased from the default price using +/- signs, i.e. -10%, and of course select the operator from: (**`^`** means the first starting X units, **`<`** means when booking is less than X units, **`>`** means when booking is greater than X units). Allowed percentages could be between -100% and +100%.

To create a new booking rate, follow these steps:

```php
$room = \App\Models\Room::find(1);
$room->newRate('15', '^', 2); // Increase unit price by 15% for the first 2 units
$room->newRate('-10', '>', 5); // Decrease unit price by 10% if booking is greater than 5 units
```

Alternatively you can create a new booking rate explicitly as follows:

```php
$room = \App\Models\Room::find(1);

// Extends \Caiocesar173\Booking\Models\BookableRate
$serviceRate = new \App\Models\ServiceRate;

$serviceRate->make(['percentage' => '15', 'operator' => '^', 'amount' => 2])
     ->bookable()->associate($room)
     ->save();
```

And here's the booking rate relations:

```php
$bookable = $serviceRate->bookable; // Get the owning resource model
```

> **Notes:**
> - All booking rate percentages should NEVER contain the `%` sign, it's known that this field is for percentage already.
> - When adding new booking rate with positive percentage, the `+` sign is NOT required, and will be omitted anyway if entered.

### Create a new custom price

Custom prices are set according to specific time based criteria. For example, let’s say you've a Coworking Space business, and one of your rooms is a Conference Room, and you would like to charge differently for both Monday and Wednesday. Will assume that Monday from 09:00 am till 05:00 pm is a peak hours, so you need to charge more, and Wednesday from 11:30 am to 03:45 pm is dead hours so you'd like to charge less! That's totally achievable through custom prices, where you can set both time frames and their prices too using +/- percentage. It works the same way as [Bookable Rates](#create-a-new-booking-rate) but on a time based criteria. Awesome, huh?

To create a custom price, follow these steps:

```php
$room = \App\Models\Room::find(1);
$room->newPrice('mon', '09:00:00', '17:00:00', '26'); // Increase pricing on Monday from 09:00 am to 05:00 pm by 26%
$room->newPrice('wed', '11:30:00', '15:45:00', '-10.5'); // Decrease pricing on Wednesday from 11:30 am to 03:45 pm by 10.5%
```

Piece of cake, right? You just set the day, from-to times, and the +/- percentage to increase/decrease your unit price.

And here's the custom price relations:

```php
$bookable = $room->bookable; // Get the owning resource model
```

> **Notes:**
> - If you don't create any custom prices, then the resource will be booked at the default resource price.
> - **Laravel Bookings** is intelegent enough to detect time format and convert if required, the above example show the explicitly correct format, but you still can write something like: '09:00 am' and it will be converted automatically for you.

### Query resource models

You can query your resource models for further details, using the intuitive API as follows:

```php
$room = \App\Models\Room::find(1);

$room->bookings; // Get all bookings
$room->pastBookings; // Get past bookings
$room->futureBookings; // Get future bookings
$room->currentBookings; // Get current bookings
$room->cancelledBookings; // Get cancelled bookings

$room->bookingsStartsBefore('2017-06-21 19:28:51')->get(); // Get bookings starts before the given date
$room->bookingsStartsAfter('2017-06-21 19:28:51')->get(); // Get bookings starts after the given date
$room->bookingsStartsBetween('2017-06-21 19:28:51', '2017-07-01 12:00:00')->get(); // Get bookings starts between the given dates

$room->bookingsEndsBefore('2017-06-21 19:28:51')->get(); // Get bookings starts before the given date
$room->bookingsEndsAfter('2017-06-21 19:28:51')->get(); // Get bookings starts after the given date
$room->bookingsEndsBetween('2017-06-21 19:28:51', '2017-07-01 12:00:00')->get(); // Get bookings starts between the given dates

$room->bookingsCancelledBefore('2017-06-21 19:28:51')->get(); // Get bookings starts before the given date
$room->bookingsCancelledAfter('2017-06-21 19:28:51')->get(); // Get bookings starts after the given date
$room->bookingsCancelledBetween('2017-06-21 19:28:51', '2017-07-01 12:00:00')->get(); // Get bookings starts between the given dates

$customer = \App\Models\Customer::find(1);
$room->bookingsOf($customer)->get(); // Get bookings of the given customer

$room->rates; // Get all bookable Rates
$room->prices; // Get all custom prices
```

All the above properties and methods are actually relationships, so you can call the raw relation methods and chain like any normal Eloquent relationship. E.g. `$room->bookings()->where('starts_at', '>', new \Carbon\Carbon())->first()`.

### Query customer models

Just like how you query your resources, you can query customers to retrieve related booking info easily. Look at these examples:

```php
$customer = \App\Models\Customer::find(1);

$customer->bookings; // Get all bookings
$customer->pastBookings; // Get past bookings
$customer->futureBookings; // Get future bookings
$customer->currentBookings; // Get current bookings
$customer->cancelledBookings; // Get cancelled bookings

$customer->bookingsStartsBefore('2017-06-21 19:28:51')->get(); // Get bookings starts before the given date
$customer->bookingsStartsAfter('2017-06-21 19:28:51')->get(); // Get bookings starts after the given date
$customer->bookingsStartsBetween('2017-06-21 19:28:51', '2017-07-01 12:00:00')->get(); // Get bookings starts between the given dates

$customer->bookingsEndsBefore('2017-06-21 19:28:51')->get(); // Get bookings starts before the given date
$customer->bookingsEndsAfter('2017-06-21 19:28:51')->get(); // Get bookings starts after the given date
$customer->bookingsEndsBetween('2017-06-21 19:28:51', '2017-07-01 12:00:00')->get(); // Get bookings starts between the given dates

$customer->bookingsCancelledBefore('2017-06-21 19:28:51')->get(); // Get bookings starts before the given date
$customer->bookingsCancelledAfter('2017-06-21 19:28:51')->get(); // Get bookings starts after the given date
$customer->bookingsCancelledBetween('2017-06-21 19:28:51', '2017-07-01 12:00:00')->get(); // Get bookings starts between the given dates

$room = \App\Models\Room::find(1);
$customer->isBooked($room); // Check if the customer booked the given room
$customer->bookingsOf($room)->get(); // Get bookings by the customer for the given room
```

Just like resource models, all the above properties and methods are actually relationships, so you can call the raw relation methods and chain like any normal Eloquent relationship. E.g. `$customer->bookings()->where('starts_at', '>', new \Carbon\Carbon())->first()`.

