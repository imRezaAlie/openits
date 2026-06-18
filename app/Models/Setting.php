<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Application setting stored in the database.
 *
 * @property int $id
 * @property string $key
 * @property string|null $value
 * @property string $type
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 */
class Setting extends Model
{
    public const KEY_GOOGLE_LOGIN_ENABLED = 'google_login_enabled';

    public const TYPE_BOOLEAN = 'boolean';

    /**
     * @var array<int, string>
     */
    protected $fillable = [
        'key',
        'value',
        'type',
    ];

    /**
     * Cast the stored value according to its type.
     */
    public function getCastValue(): mixed
    {
        return match ($this->type) {
            self::TYPE_BOOLEAN => filter_var($this->value, FILTER_VALIDATE_BOOLEAN),
            default => $this->value,
        };
    }
}
