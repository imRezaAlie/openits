<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TechnologyRadarEntry extends Model
{
    protected $fillable = [
        'technology_id',
        'ring',
        'notes',
        'updated_by',
    ];

    public function technology(): BelongsTo
    {
        return $this->belongsTo(Technology::class);
    }

    public function updatedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}
