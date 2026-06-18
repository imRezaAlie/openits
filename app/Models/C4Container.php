<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class C4Container extends Model
{
    use HasUuids;

    protected $fillable = [
        'system_id',
        'name',
        'type',
        'technology',
        'description',
        'position',
        'metadata',
        'sunset_date',
    ];

    protected function casts(): array
    {
        return [
            'position' => 'array',
            'metadata' => 'array',
            'sunset_date' => 'date',
        ];
    }

    public function system(): BelongsTo
    {
        return $this->belongsTo(System::class);
    }

    public function components(): HasMany
    {
        return $this->hasMany(C4Component::class, 'c4_container_id');
    }

    public function complianceTags(): MorphMany
    {
        return $this->morphMany(C4ComplianceTag::class, 'taggable');
    }

    public function comments(): MorphMany
    {
        return $this->morphMany(C4Comment::class, 'commentable');
    }

    public function isDeprecated(): bool
    {
        return $this->sunset_date !== null && $this->sunset_date->isPast();
    }
}
