<?php

declare(strict_types=1);

namespace Yii\Extension\User\Migration;

use Yiisoft\Yii\Db\Migration\MigrationBuilder;
use Yiisoft\Yii\Db\Migration\RevertibleMigrationInterface;

/**
 * class M200602215007CreateProfileTable
 */
final class M200602215007CreateProfileTable implements RevertibleMigrationInterface
{
    public function up(MigrationBuilder $b): void
    {
        $tableOptions = null;

        if ($b->getDb()->getDriverName() === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8mb4 ENGINE=InnoDB';
        }

        $b->createTable(
            '{{%profile}}',
            [
                'user_id' => $b->primaryKey(),
                'name' => $b->string(255),
                'public_email' => $b->string(255),
                'location' => $b->string(255),
                'website' => $b->string(255),
                'bio' => $b->text(),
                'timezone' => $b->string(40),
            ],
            $tableOptions
        );

        $b->addForeignKey(
            'fk_user_profile',
            '{{%profile}}',
            ['user_id'],
            '{{%user}}',
            ['id'],
            'CASCADE',
            'RESTRICT',
        );
    }

    public function down(MigrationBuilder $b): void
    {
        $b->dropTable('{{%profile}}');
    }
}
