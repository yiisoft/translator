<?php

declare(strict_types=1);

namespace Yii\Extension\User\Repository;

use Psr\Log\LogLevel;
use Psr\Log\LoggerInterface;
use RuntimeException;
use Yii\Extension\User\ActiveRecord\Token;
use Yiisoft\ActiveRecord\ActiveQueryInterface;
use Yiisoft\ActiveRecord\ActiveRecordFactory;
use Yiisoft\ActiveRecord\ActiveRecordInterface;
use Yiisoft\Db\Connection\Connection;
use Yiisoft\Db\Exception\Exception;
use Yiisoft\Db\Query\QueryInterface;
use Yiisoft\Security\Random;

final class RepositoryToken
{
    private ActiveRecordFactory $activeRecordFactory;
    private LoggerInterface $logger;

    public function __construct(ActiveRecordFactory $activeRecordFactory, LoggerInterface $logger)
    {
        $this->activeRecordFactory = $activeRecordFactory;
        $this->logger = $logger;
    }

    public function findToken(array $condition): QueryInterface
    {
        return $this->tokenQuery()->where($condition);
    }

    public function findTokenByCondition(array $condition): ?ActiveRecordInterface
    {
        return $this->tokenQuery()->findOne($condition);
    }

    public function findTokenById(string $id): ?ActiveRecordInterface
    {
        return $this->findTokenByCondition(['user_id' => (int) $id]);
    }

    public function findTokenByParams(string $id, string $code, int $type): ?ActiveRecordInterface
    {
        return $this->findTokenByCondition(['user_id' => (int) $id, 'code' => $code, 'type' => $type]);
    }

    public function register(string $id, int $typeToken): bool
    {
        $result = false;

        $token = $this->activeRecordFactory->createAR(Token::class);

        $token->deleteAll(['user_id' => $id, 'type' => $typeToken]);

        if ($token->getIsNewRecord() === false) {
            throw new RuntimeException('Calling "' . __CLASS__ . '::' . __METHOD__ . '" on existing user');
        }

        /** @var Connection $db */
        $db = $this->activeRecordFactory->getConnection();

        /** @psalm-suppress UndefinedInterfaceMethod */
        $transaction = $db->beginTransaction();

        try {
            $token->setAttribute('user_id', (int) $id);
            $token->setAttribute('type', $typeToken);
            $token->setAttribute('created_at', time());
            $token->setAttribute('code', Random::string());

            if (!$token->save()) {
                $transaction->rollBack();
                return false;
            }

            $transaction->commit();

            $result = true;
        } catch (Exception $e) {
            $transaction->rollBack();
            $this->logger->log(LogLevel::WARNING, $e->getMessage());
            throw $e;
        }

        return $result;
    }

    private function tokenQuery(): ActiveQueryInterface
    {
        return $this->activeRecordFactory->createQueryTo(Token::class);
    }
}
