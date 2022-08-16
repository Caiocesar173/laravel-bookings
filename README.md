<p align="center" id="project-title">
    <img width="200" src="https://cacodes.com.br/img/logo/logo.svg" align="center" alt="GitHub Readme Stats" />
</p>

# Laravel Bookings
---

This is a Laravel package which created to made to add booking functionality in your application.
This package is based uppon, [laravel-bookings](https://github.com/rinvex/laravel-bookings).

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


## Install

To install through Composer, by run the following command:

``` bash
composer require caiocesar173/custom-api-modules-laravel
```

The package will automatically register a service provider and alias.

### Autoloading

By default, the module classes are not loaded automatically. You can autoload your modules using `psr-4`. For example:

``` json
{
  "autoload": {
    "psr-4": {
      "App\\": "app/",
      "Modules\\": "Modules/",
      "Database\\Factories\\": "database/factories/",
      "Database\\Seeders\\": "database/seeders/"
  }
}
```

**Tip: don't forget to run `composer dump-autoload` afterwards.**

## Documentation

You'll find installation instructions and full documentation on [https://docs.laravelmodules.com/](https://docs.laravelmodules.com/).
