<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class System extends Model
{
    protected $fillable = [
        'vendor_id',
        'domain_id',
        'name',
        'description',
        'system_type',
        'icon',
        'parent_system_id',
    ];

    public function domain(): BelongsTo
    {
        return $this->belongsTo(Domain::class);
    }

    public function vendor(): BelongsTo
    {
        return $this->belongsTo(Vendor::class);
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(System::class, 'parent_system_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(System::class, 'parent_system_id');
    }

    /** APIs owned by this system (primary host). */
    public function ownedApis(): HasMany
    {
        return $this->hasMany(Api::class, 'owner_system_id');
    }

    /** All systems this system's APIs integrate with (via pivot). */
    public function apis(): BelongsToMany
    {
        return $this->belongsToMany(Api::class, 'api_system')->withTimestamps();
    }

    /** BPMN process diagrams for this system. */
    public function bpmns(): HasMany
    {
        return $this->hasMany(Bpmn::class);
    }

    /** Technologies used by this system (languages, Docker, nginx, etc.). */
    public function technologies(): BelongsToMany
    {
        return $this->belongsToMany(Technology::class, 'system_technology')
            ->withPivot('version')
            ->withTimestamps();
    }

    /** Infrastructure servers (database, application, web, etc.). */
    public function servers(): HasMany
    {
        return $this->hasMany(Server::class);
    }

    /** Supporting documents (manuals, specs, runbooks, etc.). */
    public function documents(): HasMany
    {
        return $this->hasMany(SystemDocument::class);
    }

    /** Platform-specific data dictionaries (bronze/silver/native layers). */
    public function platformSchemas(): HasMany
    {
        return $this->hasMany(PlatformSchema::class);
    }
}
