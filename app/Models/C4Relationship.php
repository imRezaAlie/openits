<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class C4Relationship extends Model
{
    use HasUuids;

    protected $fillable = [
        'source_id',
        'target_id',
        'source_type',
        'target_type',
        'protocol',
        'description',
        'sync',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'sync' => 'boolean',
            'metadata' => 'array',
        ];
    }
}
