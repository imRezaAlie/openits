<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FieldMapping extends Model
{
    public const DIRECTION_INBOUND = 'inbound';

    public const DIRECTION_OUTBOUND = 'outbound';

    public const DIRECTION_BIDIRECTIONAL = 'bidirectional';

    protected $fillable = [
        'platform_field_id',
        'canonical_attribute_id',
        'api_version_id',
        'direction',
        'transform_rule',
        'notes',
    ];

    public function platformField(): BelongsTo
    {
        return $this->belongsTo(PlatformField::class);
    }

    public function canonicalAttribute(): BelongsTo
    {
        return $this->belongsTo(CanonicalAttribute::class);
    }

    public function apiVersion(): BelongsTo
    {
        return $this->belongsTo(ApiVersion::class);
    }
}
