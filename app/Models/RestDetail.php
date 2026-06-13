<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RestDetail extends Model
{
    protected $fillable = [
        'api_version_id',
        'http_method',
        'request_parameters',
        'response_schema',
        'openapi_spec',
    ];

    protected $casts = [
        'request_parameters' => 'array',
        'response_schema' => 'array',
        'openapi_spec' => 'array',
    ];

    public function apiVersion(): BelongsTo
    {
        return $this->belongsTo(ApiVersion::class);
    }
}
