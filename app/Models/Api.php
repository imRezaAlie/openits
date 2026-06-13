<?php

namespace App\Models;

use App\Support\ApiTypes;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Api extends Model
{
    protected $fillable = [
        'name',
        'type',
        'description',
        'owner_system_id',
    ];

    public function ownerSystem(): BelongsTo
    {
        return $this->belongsTo(System::class, 'owner_system_id');
    }

    public function resolvedOwnerSystem(): ?System
    {
        if ($this->owner_system_id) {
            return $this->ownerSystem;
        }

        return $this->systems->first();
    }

    public function additionalSystems()
    {
        $ownerId = $this->owner_system_id ?? $this->systems->first()?->id;

        if (! $ownerId) {
            return collect();
        }

        return $this->systems->where('id', '!=', $ownerId)->values();
    }

    public function integratedSystems()
    {
        return $this->additionalSystems();
    }

    public function scopeForVendor($query, ?int $vendorId)
    {
        if (! $vendorId) {
            return $query;
        }

        return $query->whereHas('ownerSystem', fn ($q) => $q->where('vendor_id', $vendorId));
    }

    public function versions(): HasMany
    {
        return $this->hasMany(ApiVersion::class)->orderByDesc('is_default')->orderBy('version');
    }

    public function defaultVersion(): HasOne
    {
        return $this->hasOne(ApiVersion::class)->where('is_default', true);
    }

    public function resolveVersion(?int $versionId = null): ApiVersion
    {
        if ($versionId) {
            if ($this->relationLoaded('versions')) {
                $version = $this->versions->firstWhere('id', $versionId);
                if ($version) {
                    return $version;
                }
            }

            return $this->versions()->findOrFail($versionId);
        }

        if ($this->relationLoaded('defaultVersion') && $this->defaultVersion) {
            return $this->defaultVersion;
        }

        if ($this->relationLoaded('versions')) {
            return $this->versions->firstWhere('is_default', true)
                ?? $this->versions->firstOrFail();
        }

        return $this->defaultVersion ?? $this->versions()->where('is_default', true)->firstOrFail();
    }

    public function restDetail(): HasOne
    {
        return $this->hasOneThrough(
            RestDetail::class,
            ApiVersion::class,
            'api_id',
            'api_version_id',
            'id',
            'id'
        )->where('api_versions.is_default', true);
    }

    public function soapDetail(): HasOne
    {
        return $this->hasOneThrough(
            SoapDetail::class,
            ApiVersion::class,
            'api_id',
            'api_version_id',
            'id',
            'id'
        )->where('api_versions.is_default', true);
    }

    public function getEndpointUrlAttribute(): ?string
    {
        return $this->defaultVersion?->endpoint_url;
    }

    public function getRequestFormatAttribute(): ?string
    {
        return $this->defaultVersion?->request_format;
    }

    public function getResponseFormatAttribute(): ?string
    {
        return $this->defaultVersion?->response_format;
    }

    public function getAuthenticationTypeAttribute(): ?string
    {
        return $this->defaultVersion?->authentication_type;
    }

    public function getProtocolDetailsAttribute(): ?array
    {
        return $this->defaultVersion?->protocol_details;
    }

    public function systems(): BelongsToMany
    {
        return $this->belongsToMany(System::class, 'api_system')->withTimestamps();
    }

    public function tpsMetrics(): HasMany
    {
        return $this->hasMany(TpsMetric::class);
    }

    public function latestTps(): HasOne
    {
        return $this->hasOne(TpsMetric::class)->latestOfMany('recorded_at');
    }

    public function getCurrentTpsAttribute(): ?float
    {
        return $this->latestTps?->tps_value;
    }

    public function getTypeLabelAttribute(): string
    {
        return ApiTypes::label($this->type);
    }

    public function getTypeBadgeClassAttribute(): string
    {
        return ApiTypes::badgeClass($this->type);
    }

    public function isRest(): bool
    {
        return $this->type === ApiTypes::REST;
    }

    public function isSoap(): bool
    {
        return $this->type === ApiTypes::SOAP;
    }

    public function hasSwaggerSpec(): bool
    {
        return $this->isRest();
    }

    public function hasSoapSpec(): bool
    {
        return $this->isSoap();
    }

    public function hasProtocolSpec(): bool
    {
        return ApiTypes::usesProtocolDetails($this->type);
    }

    public function isNonApiIntegration(): bool
    {
        return ApiTypes::isNonApiIntegration($this->type);
    }
}
