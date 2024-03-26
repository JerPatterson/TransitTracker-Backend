<?php

namespace App\Jobs\RealtimeData;

use App\Models\Agency;
use Cron\CronExpression;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

class DispatchAgency implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $queue = 'realtime-download';

    public Carbon $dispatchedAt;

    /**
     * Create a new job instance.
     */
    public function __construct(public Agency $agency, public bool $forceRefresh = false)
    {
        $this->dispatchedAt = now();
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        // Get the time
        $time = time();

        // Check if agency is due and if refresh is active
        // Skip verification if force refresh
        if (! $this->forceRefresh) {
            $cron = new CronExpression($this->agency->cron_schedule);
            // Use the dispatchedAt time. If there is a delay on the queue, it will still respect the normal refresh time
            if (! $cron->isDue($this->dispatchedAt) || ! $this->agency->refresh_is_active) {
                return;
            }
        }

        $response = Http::withHeaders($this->agency->headers ?? [])->get($this->agency->realtime_url);

        if ($response->failed()) {
            return;
        }

        Storage::put("realtime/{$this->agency->slug}", $response->body());

        $handler = match ($this->agency->realtime_type) {
            'gtfsrt' => GtfsRtHandler::class,
            'javascript-gtfsrt' => JavascriptGtfsRtHandler::class,
            'nextbus-json' => NextbusJsonHandler::class,
        };

        // Dispatch on realtime-process
        // Two separated queues to allow process to run as quick as possible after download
        $handler::dispatch($this->agency, $time);
    }
}
