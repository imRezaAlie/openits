<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Domain extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'description',
        'icon',
        'color',
    ];

    public function systems(): HasMany
    {
        return $this->hasMany(System::class);
    }
}
