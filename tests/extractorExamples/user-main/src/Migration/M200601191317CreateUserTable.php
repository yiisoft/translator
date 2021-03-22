<?php

declare(strict_types=1);

namespace Yii\Extension\User\Migration;

use Yiisoft\Yii\Db\Migration\MigrationBuilder;
use Yiisoft\Yii\Db\Migration\RevertibleMigrationInterface;

/**
 * Class M200601191317CreateUserTable
 */
final class M200601191317CreateUserTable implements RevertibleMigrationInterface
{
    public function up(MigrationBuilder $b): void
    {
        $tableOptions = null;

        if ($b->getDb()->getDriverName() === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8mb4 ENGINE=InnoDB';
        }

        $b->createTable(
            '{{%user}}',
            [
                'id' => $b->primaryKey(),
                'username' => $b->string(255)->notNull(),
                'email' => $b->string(255)->notNull(),
                'password_hash' => $b->string(100)->notNull(),
                'auth_key' => $b->string(32),
                'confirmed_at' => $b->integer(),
                'unconfirmed_email' => $b->string(255),
                'blocked_at' => $b->integer(),
                'registration_ip' => $b->string(45),
                'created_at' => $b->integer(),
                'updated_at' => $b->integer(),
                'flags' => $b->integer(),
                'ip_last_login' => $b->string(45),
                'last_login_at' => $b->integer(),
                'last_logout_at' => $b->integer(),
            ],
            $tableOptions
        );

        $b->createIndex('user_unique_email', '{{%user}}', ['email'], true);
        $b->createIndex('user_unique_username', '{{%user}}', ['username'], true);
    }

    public function down(MigrationBuilder $b): void
    {
        $b->dropTable('{{%user}}');
    }
}
