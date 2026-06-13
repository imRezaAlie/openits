<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TpsMetric extends Model
{
    protected $fillable = [
        'api_id',
        'tps_value',
        'recorded_at',
        'notes',
    ];

    protected $casts = [
        'tps_value' => 'decimal:2',
        'recorded_at' => 'datetime',
    ];

    public function api(): BelongsTo
    {
        return $this->belongsTo(Api::class);
    }
}
