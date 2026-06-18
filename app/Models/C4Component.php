<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class C4Component extends Model
{
    use HasUuids;

    protected $fillable = [
        'c4_container_id',
        'name',
        'type',
        'technology',
        'description',
        'dependencies',
        'position',
        'metadata',
        'sunset_date',
    ];

    protected function casts(): array
    {
        return [
            'dependencies' => 'array',
            'position' => 'array',
            'metadata' => 'array',
            'sunset_date' => 'date',
        ];
    }

    public function container(): BelongsTo
    {
        return $this->belongsTo(C4Container::class, 'c4_container_id');
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
