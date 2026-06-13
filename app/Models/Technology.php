<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Technology extends Model
{
    protected $fillable = [
        'name',
        'category',
        'icon',
    ];

    public function systems(): BelongsToMany
    {
        return $this->belongsToMany(System::class, 'system_technology')
            ->withPivot('version')
            ->withTimestamps();
    }
}
