<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PlatformField extends Model
{
    protected $fillable = [
        'platform_schema_id',
        'native_name',
        'native_path',
        'data_type',
        'description',
        'is_primary_key',
        'nullable',
        'sample_value',
        'metadata',
        'sort_order',
    ];

    protected $casts = [
        'is_primary_key' => 'boolean',
        'nullable' => 'boolean',
        'metadata' => 'array',
    ];

    public function schema(): BelongsTo
    {
        return $this->belongsTo(PlatformSchema::class, 'platform_schema_id');
    }

    public function mappings(): HasMany
    {
        return $this->hasMany(FieldMapping::class);
    }
}
