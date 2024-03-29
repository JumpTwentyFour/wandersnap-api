<?php

declare(strict_types=1);

namespace App\Providers;

use App\Domain\Locations\Models\Location;
use App\Domain\Trips\Models\Trip;
use App\Policies\LocationPolicy;
use App\Policies\TripPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        Trip::class => TripPolicy::class,
        Location::class => LocationPolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
    }
}
