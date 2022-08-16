<?php

namespace Caiocesar173\Booking\Providers;

use Illuminate\Support\ServiceProvider;
use Caiocesar173\Utils\Traits\RepositoryServiceProviderTrait;

class BookingServiceProvider extends ServiceProvider
{
    use RepositoryServiceProviderTrait;

    public function boot()
    {
        $this->loadMigrationsFrom(__DIR__ . '/../../Database/Migrations');
    }

    public function register()
    {
        $this->registerLocalRepository(__DIR__ . '/../Repositories');
    }
}
