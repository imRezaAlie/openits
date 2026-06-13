<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

class Vendor extends Model
{
    use HasFactory;

    protected $fillable = ['name'];

    public function systems(): HasMany
    {
        return $this->hasMany(System::class);
    }

    public function projects(): HasMany
    {
        return $this->hasMany(Project::class);
    }

    public function apis(): HasManyThrough
    {
        return $this->hasManyThrough(Api::class, System::class, 'vendor_id', 'owner_system_id');
    }
}
