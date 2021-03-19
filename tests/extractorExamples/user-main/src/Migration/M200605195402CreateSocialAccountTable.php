<?php

declare(strict_types=1);

namespace Yii\Extension\User\Migration;

use Yiisoft\Yii\Db\Migration\MigrationBuilder;
use Yiisoft\Yii\Db\Migration\RevertibleMigrationInterface;

/**
 * class M200605195402CreateSocialAccountTable
 */
final class M200605195402CreateSocialAccountTable implements RevertibleMigrationInterface
{
    public function up(MigrationBuilder $b): void
    {
        $tableOptions = null;

        if ($b->getDb()->getDriverName() === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8mb4 ENGINE=InnoDB';
        }

        $b->createTable(
            '{{%social_account}}',
            [
                'id' => $b->primaryKey(),
                'user_id' => $b->integer(),
                'provider' => $b->string(255),
                'client_id' => $b->string(255),
                'data' => $b->text(),
                'code' => $b->string(32),
                'created_at' => $b->integer(),
                'email' => $b->string(255),
                'username' => $b->string(255),
            ],
            $tableOptions
        );

        $b->createIndex('account_unique', '{{%social_account}}', ['provider', 'client_id'], true);
        $b->createIndex('account_unique_code', '{{%social_account}}', ['code'], true);

        $b->addForeignKey(
            'fk_user_account',
            '{{%social_account}}',
            ['user_id'],
            '{{%user}}',
            ['id'],
            'CASCADE',
            'RESTRICT',
        );
    }

    public function down(MigrationBuilder $b): void
    {
        $b->dropTable('{{%social_account}}');
    }
}
