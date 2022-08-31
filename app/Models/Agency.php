<?php

namespace App\Models;

use App\Events\VehiclesUpdated;
use App\Models\Tag;
use Arr;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Spatie\ResponseCache\Facades\ResponseCache;
use URL;

class Agency extends Model
{
    protected $fillable = ['name', 'short_name', 'slug', 'static_gtfs_url', 'realtime_url', 'realtime_type',
        'realtime_options', 'color', 'text_color', 'vehicles_type', 'is_active', 'license',
        'short_name', 'refresh_is_active', 'cron_schedule', 'cities', 'static_etag', ];

    protected $casts = [
        'tags' => 'array',
        'license' => 'array',
        'cities' => 'array',
    ];

    public function getRouteKeyName()
    {
        return 'slug';
    }

    /*
     * Relationships
     */
    public function vehicles(): HasMany
    {
        return $this->hasMany(Vehicle::class);
    }

    public function tags(): MorphToMany
    {
        return $this->morphToMany(Tag::class, 'taggable');
    }

    public function trips(): HasMany
    {
        return $this->hasMany(Trip::class);
    }

    public function routes(): HasMany
    {
        return $this->hasMany(Route::class);
    }

    public function services(): HasMany
    {
        return $this->hasMany(Service::class);
    }

    public function regions(): BelongsToMany
    {
        return $this->belongsToMany(Region::class);
    }

    public function links(): BelongsToMany
    {
        return $this->belongsToMany(Link::class);
    }

    public function notificationUsers(): BelongsToMany
    {
        return $this->belongsToMany(NotificationUser::class);
    }

    public function activeNotificationUsers(): BelongsToMany
    {
        return $this->belongsToMany(NotificationUser::class)->active();
    }

    public function exoWithVin(): HasMany
    {
        return $this->hasMany(Vehicle::class)->exoWithVin();
    }

    public function exoLabelledVehicles(): HasMany
    {
        return $this->hasMany(Vehicle::class)->exoLabelled();
    }

    public function exoUnlabelledVehicles(): HasMany
    {
        return $this->hasMany(Vehicle::class)->exoUnlabelled();
    }

    /*
     * Accessors
     */
    public function getRealtimeMethodAttribute()
    {
        return json_decode($this->realtime_options)->realtime_method;
    }

    public function getHeaderNameAttribute()
    {
        return json_decode($this->realtime_options)->header_name;
    }

    public function getHeaderValueAttribute()
    {
        return json_decode($this->realtime_options)->header_value;
    }

    public function getParamNameAttribute()
    {
        return json_decode($this->realtime_options)->param_name;
    }

    public function getParamValueAttribute()
    {
        return json_decode($this->realtime_options)->param_value;
    }

    public function getDownloadMethodAttribute()
    {
        return json_decode($this->realtime_options)->download_method ?? '';
    }

    /*
     * Scopes
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', 1);
    }

    /*
     * Others
     */
    public function getRandomCitiesAttribute(): array
    {
        $cities = $this->cities;
        if (! $cities) {
            return [];
        }

        if (count($cities) < 5) {
            return $cities;
        }

        return Arr::random($cities, 5);
    }

    public function signedUpdateGtfsUrl(): string
    {
        return URL::temporarySignedRoute('admin.agencies.update', now()->addHours(12), [
            'agency' => $this,
        ]);
    }

    /*
     * Events
     */
    protected $dispatchesEvents = [
        'updated' => VehiclesUpdated::class,
    ];

    protected static function booted(): void
    {
        static::updated(function (self $agency) {
            ResponseCache::forget('/api/regions');
            ResponseCache::forget('/v1/regions');
            ResponseCache::forget("/api/vehicles/{$agency->slug}");
            ResponseCache::forget("/v1/vehicles/{$agency->slug}");

            ResponseCache::selectCachedItems()
                ->usingSuffix('en')
                ->forUrls('/v2/agencies', "/v2/agencies/{$agency->slug}", "/v2/agencies/{$agency->slug}/vehicles", '/v2/regions')
                ->forget();
            ResponseCache::selectCachedItems()
                ->usingSuffix('fr')
                ->forUrls('/v2/agencies', "/v2/agencies/{$agency->slug}", "/v2/agencies/{$agency->slug}/vehicles", '/v2/regions')
                ->forget();
        });
    }
}
