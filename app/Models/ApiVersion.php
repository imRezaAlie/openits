<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class ApiVersion extends Model
{
    protected $fillable = [
        'api_id',
        'version',
        'endpoint_url',
        'description',
        'request_format',
        'response_format',
        'authentication_type',
        'protocol_details',
        'status',
        'is_default',
    ];

    protected $casts = [
        'protocol_details' => 'array',
        'is_default' => 'boolean',
    ];

    public function api(): BelongsTo
    {
        return $this->belongsTo(Api::class);
    }

    public function restDetail(): HasOne
    {
        return $this->hasOne(RestDetail::class);
    }

    public function soapDetail(): HasOne
    {
        return $this->hasOne(SoapDetail::class);
    }

    public function getStatusBadgeClassAttribute(): string
    {
        return match ($this->status) {
            'deprecated' => 'warning',
            'draft' => 'secondary',
            default => 'success',
        };
    }
}
