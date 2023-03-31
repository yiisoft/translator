<?php

declare(strict_types=1);

namespace Yii\Extension\User\ActiveRecord;

use RuntimeException;
use Yiisoft\ActiveRecord\ActiveQuery;
use Yiisoft\ActiveRecord\ActiveRecord;

/**
 * Token Active Record - Module AR User.
 *
 * Database fields:
 *
 * @property int $user_id
 * @property string  $code
 * @property int $created_at
 * @property int $type
 * @property string  $url
 * @property bool    $isExpired
 */
final class Token extends ActiveRecord
{
    public const TYPE_CONFIRMATION = 0;
    public const TYPE_RECOVERY = 1;
    public const TYPE_CONFIRM_NEW_EMAIL = 2;
    public const TYPE_CONFIRM_OLD_EMAIL = 3;

    public function tableName(): string
    {
        return '{{%token}}';
    }

    public function getUser(): ActiveQuery
    {
        return $this->hasOne(User::class, ['id' => 'user_id']);
    }

    public function getCode(): string
    {
        return (string) $this->getAttribute('code');
    }

    public function getType(): int
    {
        return (int) $this->getAttribute('type');
    }

    public function getUserId(): string
    {
        return (string) $this->getAttribute('user_id');
    }

    public function toUrl(): string
    {
        return match ($this->getAttribute('type')) {
            self::TYPE_CONFIRMATION => 'confirm',
            self::TYPE_RECOVERY => 'reset',
            self::TYPE_CONFIRM_NEW_EMAIL, self::TYPE_CONFIRM_OLD_EMAIL => 'email/attempt',
            default => throw new RuntimeException('Url not available.'),
        };
    }

    public function isExpired(int $tokenConfirmWithin = 0, int $tokenRecoverWithin = 0): bool
    {
        $expirationTime = match ($this->getAttribute('type')) {
            self::TYPE_CONFIRMATION, self::TYPE_CONFIRM_NEW_EMAIL, self::TYPE_CONFIRM_OLD_EMAIL => $tokenConfirmWithin,
            self::TYPE_RECOVERY => $tokenRecoverWithin,
            default => throw new RuntimeException('Expired not available.'),
        };

        return ($this->created_at + $expirationTime) < time();
    }

    public function primaryKey(): array
    {
        return ['user_id', 'code', 'type'];
    }
}
