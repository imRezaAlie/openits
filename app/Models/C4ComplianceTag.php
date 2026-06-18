<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class C4ComplianceTag extends Model
{
    use HasUuids;

    protected $fillable = [
        'taggable_type',
        'taggable_id',
        'tag',
        'notes',
    ];

    public function taggable()
    {
        return $this->morphTo();
    }
}
