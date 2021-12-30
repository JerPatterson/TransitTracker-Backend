<?php

namespace App\Notifications\Push;

use App\Models\Vehicle;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Channels\DatabaseChannel;
use Illuminate\Notifications\Notification;
use NotificationChannels\WebPush\WebPushChannel;
use NotificationChannels\WebPush\WebPushMessage;

class ElectricStmAppearance extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(private Vehicle $vehicle)
    {
        $this->vehicle->load('trip');
    }

    public function via()
    {
        return [WebPushChannel::class, DatabaseChannel::class];
    }

    public function viaQueues()
    {
        return [
            WebPushChannel::class => 'notifications',
        ];
    }

    public function toWebPush()
    {
        return (new WebPushMessage)
            ->icon('https://api.transittracker.ca/img/icon-192.png')
            ->badge('https://api.transittracker.ca/img/badge.png')
            ->title(__('push.electric_stm.title', ['label' => $this->vehicle->vehicle, 'headsign' => $this->vehicle->trip->trip_headsign]))
            ->body(__('push.electric_stm.body', ['label' => $this->vehicle->vehicle, 'route' => "{$this->vehicle->trip->route_short_name} {$this->vehicle->trip->route_long_name}"]))
            ->action(__('push.electric_stm.action_track', []), "open_vehicle.{$this->vehicle->agency->regions[0]->slug}.{$this->vehicle->id}")
            ->action(__('push.electric_stm.action_gtfstools', []), "open_gtfstools.{$this->vehicle->gtfs_trip}");
    }

    public function toArray()
    {
        return [
            'title' => __('push.electric_stm.title', ['label' => $this->vehicle->vehicle, 'headsign' => $this->vehicle->trip->trip_headsign]),
            'body' => __('push.electric_stm.body', ['label' => $this->vehicle->vehicle, 'route' => "{$this->vehicle->trip->route_short_name} {$this->vehicle->trip->route_long_name}"]),
        ];
    }
}
