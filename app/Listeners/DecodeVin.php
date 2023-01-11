<?php

namespace App\Listeners;

use App\Events\VehicleCreated;
use App\Jobs\Vin\DecodeVin as DecodeVinJob;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Http;

class DecodeVin implements ShouldQueue
{
    public $queue = 'misc';

    public function __construct()
    {
    }

    public function handle(VehicleCreated $event)
    {
        if (! $event->vehicle->isExoVin()) {
            return false;
        }

        $response = Http::asForm()->post('https://vpic.nhtsa.dot.gov/api/vehicles/DecodeVINValuesBatch/', [
            'DATA' => $event->vehicle->vehicle,
            'format' => 'JSON',
        ]);

        DecodeVinJob::dispatchSync($response->json()['Results'][0]);
    }
}
