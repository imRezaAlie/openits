<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CanonicalAttribute extends Model
{
    protected $fillable = [
        'canonical_entity_id',
        'name',
        'slug',
        'data_type',
        'description',
        'is_required',
        'constraints',
        'sort_order',
    ];

    protected $casts = [
        'is_required' => 'boolean',
        'constraints' => 'array',
    ];

    public function entity(): BelongsTo
    {
        return $this->belongsTo(CanonicalEntity::class, 'canonical_entity_id');
    }

    public function fieldMappings(): HasMany
    {
        return $this->hasMany(FieldMapping::class);
    }
}
