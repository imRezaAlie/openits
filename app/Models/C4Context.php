<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class C4Context extends Model
{
    use HasUuids;

    protected $fillable = [
        'name',
        'description',
        'external_systems',
        'users',
        'position',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'external_systems' => 'array',
            'users' => 'array',
            'position' => 'array',
            'metadata' => 'array',
        ];
    }

    public function system(): HasOne
    {
        return $this->hasOne(System::class, 'c4_context_id');
    }

    public function relationships(): HasMany
    {
        return $this->hasMany(C4Relationship::class, 'source_id')
            ->where('source_type', 'context');
    }
}
