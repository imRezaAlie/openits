<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SoapDetail extends Model
{
    protected $fillable = [
        'api_version_id',
        'wsdl_url',
        'namespace',
        'soap_action',
        'method_name',
        'operation_spec',
    ];

    protected $casts = [
        'operation_spec' => 'array',
    ];

    public function apiVersion(): BelongsTo
    {
        return $this->belongsTo(ApiVersion::class);
    }
}
