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
        switch ($this->getAttribute('type')) {
            case self::TYPE_CONFIRMATION:
                $route = 'confirm';
                break;
            case self::TYPE_RECOVERY:
                $route = 'reset';
                break;
            case self::TYPE_CONFIRM_NEW_EMAIL:
            case self::TYPE_CONFIRM_OLD_EMAIL:
                $route = 'email/attempt';
                break;
            default:
                throw new RuntimeException('Url not available.');
        }

        return $route;
    }

    public function isExpired(int $tokenConfirmWithin = 0, int $tokenRecoverWithin = 0): bool
    {
        switch ($this->getAttribute('type')) {
            case self::TYPE_CONFIRMATION:
            case self::TYPE_CONFIRM_NEW_EMAIL:
            case self::TYPE_CONFIRM_OLD_EMAIL:
                $expirationTime = $tokenConfirmWithin;
                break;
            case self::TYPE_RECOVERY:
                $expirationTime = $tokenRecoverWithin;
                break;
            default:
                throw new RuntimeException('Expired not available.');
        }

        return ($this->created_at + $expirationTime) < time();
    }

    public function primaryKey(): array
    {
        return ['user_id', 'code', 'type'];
    }
}
