<?php

namespace App\Providers;

use App\Events\ElectricStmVehicleUpdated;
use App\Events\VehicleCreated;
use App\Listeners\DeactivateInactiveSubscription;
use App\Listeners\SendElectricStmNotification;
use App\Listeners\SendNewVehicleNotification;
use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Listeners\SendEmailVerificationNotification;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use NotificationChannels\WebPush\Events\NotificationFailed;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event listener mappings for the application.
     *
     * @var array
     */
    protected $listen = [
        Registered::class => [
            SendEmailVerificationNotification::class,
        ],
        NotificationFailed::class => [
            DeactivateInactiveSubscription::class,
        ],
        VehicleCreated::class => [
            SendNewVehicleNotification::class,
        ],
        ElectricStmVehicleUpdated::class => [
            SendElectricStmNotification::class,
        ],
    ];

    /**
     * Register any events for your application.
     *
     * @return void
     */
    public function boot()
    {
        //
    }
}
