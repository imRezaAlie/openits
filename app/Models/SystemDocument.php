<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class SystemDocument extends Model
{
    protected static function booted(): void
    {
        static::deleting(function (SystemDocument $document) {
            $document->deleteAttachment();
        });
    }

    protected $fillable = [
        'system_id',
        'name',
        'version',
        'attachment_path',
        'attachment_original_name',
    ];

    public function system(): BelongsTo
    {
        return $this->belongsTo(System::class);
    }

    public function deleteAttachment(): void
    {
        if ($this->attachment_path && Storage::disk('local')->exists($this->attachment_path)) {
            Storage::disk('local')->delete($this->attachment_path);
        }
    }
}
