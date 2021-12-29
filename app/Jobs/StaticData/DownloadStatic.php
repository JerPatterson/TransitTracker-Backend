<?php

namespace App\Jobs\StaticData;

use App\Models\Agency;
use GuzzleHttp\Client;
use Illuminate\Bus\Batchable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class DownloadStatic implements ShouldQueue
{
    use Batchable, Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(private Agency $agency)
    {
    }

    public function handle()
    {
        // Set path
        $cwd = getcwd();
        $time = time();
        $fileName = "{$cwd}/storage/app/static/{$this->agency->slug}-{$time}.zip";

        // Download GTFS
        $client = new Client();
        $response = $client->get($this->agency->static_gtfs_url, ['sink' => $fileName, 'headers' => [
            'If-None-Match' => $this->agency->static_etag,
        ]]);

        if ($response->hasHeader('ETag')) {
            $this->agency->static_etag = $response->getHeader('ETag')[0];
            $this->agency->saveQuietly();
        }

        if ($response->getStatusCode() === 304) {
            // 304 = same data
            // Do not continue

            $client = null;

            $this->batch()->cancel();

            return false;
        }

        // Dispatch extraction
        $this->batch()->add([new ExtractAndDispatchStaticGtfs($this->agency, $fileName)]);

        // Erase client
        $client = null;
    }
}
