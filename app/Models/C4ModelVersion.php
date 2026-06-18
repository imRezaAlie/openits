<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class C4ModelVersion extends Model
{
    use HasUuids;

    protected $fillable = [
        'system_id',
        'user_id',
        'commit_message',
        'snapshot',
        'branch',
        'version_number',
    ];

    protected function casts(): array
    {
        return [
            'snapshot' => 'array',
        ];
    }

    public function system(): BelongsTo
    {
        return $this->belongsTo(System::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
