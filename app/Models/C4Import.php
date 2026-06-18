<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class C4Import extends Model
{
    use HasUuids;

    public const STATUS_PENDING = 'pending';

    public const STATUS_PROCESSING = 'processing';

    public const STATUS_COMPLETED = 'completed';

    public const STATUS_FAILED = 'failed';

    protected $fillable = [
        'system_id',
        'user_id',
        'type',
        'status',
        'file_path',
        'original_filename',
        'progress',
        'result',
        'error_message',
        'options',
        'started_at',
        'completed_at',
    ];

    protected function casts(): array
    {
        return [
            'result' => 'array',
            'options' => 'array',
            'started_at' => 'datetime',
            'completed_at' => 'datetime',
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

    public function markProcessing(): void
    {
        $this->update([
            'status' => self::STATUS_PROCESSING,
            'started_at' => now(),
            'progress' => 5,
        ]);
    }

    public function updateProgress(int $progress): void
    {
        $this->update(['progress' => min(99, max(0, $progress))]);
    }

    /**
     * @param  array<string, mixed>  $result
     */
    public function markCompleted(array $result): void
    {
        $this->update([
            'status' => self::STATUS_COMPLETED,
            'progress' => 100,
            'result' => $result,
            'completed_at' => now(),
        ]);
    }

    public function markFailed(string $message): void
    {
        $this->update([
            'status' => self::STATUS_FAILED,
            'error_message' => $message,
            'completed_at' => now(),
        ]);
    }

    public function isFinished(): bool
    {
        return in_array($this->status, [self::STATUS_COMPLETED, self::STATUS_FAILED], true);
    }
}
