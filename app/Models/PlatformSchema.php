<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PlatformSchema extends Model
{
    protected $fillable = [
        'system_id',
        'name',
        'slug',
        'description',
        'data_layer',
        'source_type',
        'version',
        'metadata',
    ];

    protected $casts = [
        'metadata' => 'array',
    ];

    public function system(): BelongsTo
    {
        return $this->belongsTo(System::class);
    }

    public function fields(): HasMany
    {
        return $this->hasMany(PlatformField::class)->orderBy('sort_order');
    }
}
