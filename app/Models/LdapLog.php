<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Audit log entry for LDAP authentication and sync operations.
 *
 * @property int $id
 * @property string|null $username
 * @property string|null $domain
 * @property string $action
 * @property string $status
 * @property string|null $ip_address
 * @property string|null $message
 * @property array<string, mixed>|null $context
 * @property int|null $user_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 */
class LdapLog extends Model
{
    public const ACTION_LOGIN = 'login';

    public const ACTION_SYNC = 'sync';

    public const ACTION_TEST = 'test';

    public const ACTION_TOGGLE = 'toggle';

    public const STATUS_SUCCESS = 'success';

    public const STATUS_FAILURE = 'failure';

    /**
     * @var array<int, string>
     */
    protected $fillable = [
        'username',
        'domain',
        'action',
        'status',
        'ip_address',
        'message',
        'context',
        'user_id',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'context' => 'array',
        ];
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
