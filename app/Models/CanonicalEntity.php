<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CanonicalEntity extends Model
{
    protected $fillable = [
        'domain_id',
        'name',
        'slug',
        'description',
    ];

    public function domain(): BelongsTo
    {
        return $this->belongsTo(Domain::class);
    }

    public function attributes(): HasMany
    {
        return $this->hasMany(CanonicalAttribute::class)->orderBy('sort_order');
    }
}
