<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Prunable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use URL;

class FailedJob extends Model
{
    protected $table = 'failed_jobs_histories';

    protected $fillable = ['name', 'agency_id', 'snooze', 'exception'];

    public function agency(): BelongsTo
    {
        return $this->belongsTo(Agency::class);
    }

    public function prunable(): Builder
    {
        return static::where('created_at', '<=', now()->subMonth());
    }

    public function signedSnoozeUrl(int $hours): string
    {
        return URL::temporarySignedRoute('signed.snooze', now()->addHours(5), [
            'failedJob' => $this,
            'hours' => $hours,
        ]);
    }
}
