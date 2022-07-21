<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Stat extends Model
{
    protected $fillable = ['type', 'data', 'region_id'];

    protected $casts = [
        'data' => 'object',
    ];

    public function region(): BelongsTo
    {
        return $this->belongsTo(Region::class);
    }
}
