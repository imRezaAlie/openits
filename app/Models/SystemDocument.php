<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

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

    public function isMarkdown(): bool
    {
        return str_ends_with(strtolower($this->attachment_original_name), '.md');
    }

    public function readContent(): ?string
    {
        if (! $this->attachment_path || ! Storage::disk('local')->exists($this->attachment_path)) {
            return null;
        }

        return Storage::disk('local')->get($this->attachment_path);
    }

    public function writeContent(string $content): void
    {
        if ($this->attachment_path) {
            Storage::disk('local')->put($this->attachment_path, $content);

            return;
        }

        $filename = self::filenameForName($this->name);
        $path = "system-documents/{$this->system_id}/{$filename}";

        Storage::disk('local')->put($path, $content);

        $this->forceFill([
            'attachment_path' => $path,
            'attachment_original_name' => $filename,
        ])->save();
    }

    public static function filenameForName(string $name): string
    {
        $base = Str::slug($name) ?: 'document';

        return $base.'.md';
    }
}
