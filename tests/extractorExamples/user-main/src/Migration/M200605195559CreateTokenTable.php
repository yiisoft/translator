<?php

declare(strict_types=1);

namespace Yii\Extension\User\Migration;

use Yiisoft\Yii\Db\Migration\MigrationBuilder;
use Yiisoft\Yii\Db\Migration\RevertibleMigrationInterface;

/**
 * class M200605195559CreateTokenTable
 */
final class M200605195559CreateTokenTable implements RevertibleMigrationInterface
{
    public function up(MigrationBuilder $b): void
    {
        $tableOptions = null;

        if ($b->getDb()->getDriverName() === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8mb4 ENGINE=InnoDB';
        }

        $b->createTable(
            '{{%token}}',
            [
                'user_id' => $b->integer()->notNull(),
                'code' => $b->string(32)->notNull(),
                'created_at' => $b->integer()->notNull(),
                'type' => $b->smallInteger()->notNull(),
            ],
            $tableOptions
        );

        $b->createIndex('token_unique', '{{%token}}', ['user_id', 'code', 'type'], true);

        $b->addForeignKey(
            'fk_user_token',
            '{{%token}}',
            ['user_id'],
            '{{%user}}',
            ['id'],
            'CASCADE',
            'RESTRICT',
        );
    }

    public function down(MigrationBuilder $b): void
    {
        $b->dropTable('{{%token}}');
    }
}
