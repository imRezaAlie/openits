<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class C4ChangeRequest extends Model
{
    use HasUuids;

    protected $fillable = [
        'system_id',
        'requester_id',
        'reviewer_id',
        'title',
        'description',
        'impact',
        'status',
        'reviewer_notes',
        'snapshot',
        'submitted_at',
        'reviewed_at',
    ];

    protected function casts(): array
    {
        return [
            'snapshot' => 'array',
            'submitted_at' => 'datetime',
            'reviewed_at' => 'datetime',
        ];
    }

    public function system(): BelongsTo
    {
        return $this->belongsTo(System::class);
    }

    public function requester(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requester_id');
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewer_id');
    }

    public function isEditable(): bool
    {
        return in_array($this->status, ['draft', 'changes_requested'], true);
    }
}
