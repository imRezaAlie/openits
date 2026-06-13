<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Server extends Model
{
    protected $fillable = [
        'system_id',
        'name',
        'server_type',
        'hostname',
        'ip_address',
        'port',
        'location',
        'ram',
        'cpu',
        'nic',
        'ssl_certificate',
        'ssl_issued_at',
        'ssl_expires_at',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'ssl_issued_at' => 'date',
            'ssl_expires_at' => 'date',
        ];
    }

    public function system(): BelongsTo
    {
        return $this->belongsTo(System::class);
    }

    public function displayName(): string
    {
        return $this->name ?: ($this->hostname ?: ($this->ip_address ?: 'Server #'.$this->id));
    }
}
