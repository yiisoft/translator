<?php

declare(strict_types=1);

namespace Yii\Extension\User\Repository;

use LasseRafn\InitialAvatarGenerator\InitialAvatar;
use Psr\Log\LogLevel;
use Psr\Log\LoggerInterface;
use RuntimeException;
use Yii\Extension\User\ActiveRecord\Profile;
use Yii\Extension\User\ActiveRecord\Token;
use Yii\Extension\User\ActiveRecord\User;
use Yii\Extension\User\Form\FormRegister;
use Yiisoft\ActiveRecord\ActiveQueryInterface;
use Yiisoft\ActiveRecord\ActiveRecordFactory;
use Yiisoft\ActiveRecord\ActiveRecordInterface;
use Yiisoft\Aliases\Aliases;
use Yiisoft\Auth\IdentityInterface;
use Yiisoft\Auth\IdentityRepositoryInterface;
use Yiisoft\Db\Connection\Connection;
use Yiisoft\Db\Exception\Exception;
use Yiisoft\Db\Query\QueryInterface;
use Yiisoft\Files\FileHelper;
use Yiisoft\Router\UrlGeneratorInterface;
use Yiisoft\Security\Random;

use function array_rand;
use function count;
use function filter_var;
use function str_shuffle;
use function str_split;

final class RepositoryUser implements IdentityRepositoryInterface
{
    private ActiveRecordFactory $activeRecordFactory;
    private Aliases $aliases;
    private InitialAvatar $avatar;
    private LoggerInterface $logger;
    private Profile $profile;
    private Token $token;
    private User $user;

    /**
     * @psalm-suppress PropertyTypeCoercion
     */
    public function __construct(
        ActiveRecordFactory $activeRecordFactory,
        Aliases $aliases,
        InitialAvatar $avatar,
        LoggerInterface $logger
    ) {
        $this->activeRecordFactory = $activeRecordFactory;
        $this->aliases = $aliases;
        $this->avatar = $avatar;
        $this->logger = $logger;
        $this->token = $activeRecordFactory->createAR(Token::class);
        $this->profile = $activeRecordFactory->createAR(Profile::class);
        $this->user = $activeRecordFactory->createAR(User::class);
    }

    /**
     * @param string $id
     *
     * @return IdentityInterface|null
     *
     * @psalm-suppress InvalidReturnType, InvalidReturnStatement
     */
    public function findIdentity(string $id): ?IdentityInterface
    {
        return $this->findUserByCondition(['id' => $id]);
    }

    public function findUser(array $condition): QueryInterface
    {
        return $this->userQuery()->where($condition);
    }

    public function findUserByCondition(array $condition): ?ActiveRecordInterface
    {
        return $this->userQuery()->findOne($condition);
    }

    public function findUserById(string $id): ?ActiveRecordInterface
    {
        return $this->findUserByCondition(['id' => $id]);
    }

    public function findUserByEmail(string $email): ?ActiveRecordInterface
    {
        return $this->findUserByCondition(['email' => $email]);
    }

    public function findUserByUsername(string $username): ?ActiveRecordInterface
    {
        return $this->findUserByCondition(['username' => $username]);
    }

    public function findUserByUsernameOrEmail(string $usernameOrEmail): ?ActiveRecordInterface
    {
        if (filter_var($usernameOrEmail, FILTER_VALIDATE_EMAIL)) {
            return $this->findUserByEmail($usernameOrEmail);
        }

        return $this->findUserByUsername($usernameOrEmail);
    }

    public function generateUrlToken(UrlGeneratorInterface $url, bool $isConfirmation): ?string
    {
        $urlToken = null;

        if ($isConfirmation === true) {
            $urlToken = $url->generateAbsolute(
                $this->token->toUrl(),
                [
                    'id' => $this->token->getUserId(),
                    'code' => $this->token->getCode(),
                ]
            );
        }

        return $urlToken;
    }

    public function register(FormRegister $formRegister, bool $isConfirmation, bool $isGeneratingPassword): bool
    {
        if ($this->user->getIsNewRecord() === false) {
            throw new RuntimeException('Calling "' . __CLASS__ . '::' . __METHOD__ . '" on existing user');
        }

        /** @var Connection $db */
        $db = $this->activeRecordFactory->getConnection();
        $transaction = $db->beginTransaction();

        try {
            $password = $formRegister->getPassword();

            if ($isGeneratingPassword) {
                $password = $this->generate(8);
                $formRegister->password($password);
            }

            $this->user->username($formRegister->getUsername());
            $this->user->email($formRegister->getEmail());
            $this->user->unconfirmedEmail(null);
            $this->user->password($password);
            $this->user->passwordHash($password);
            $this->user->authKey();
            $this->user->registrationIp($formRegister->getIp());

            if ($isConfirmation === false) {
                $this->user->confirmedAt();
            }

            $this->user->createdAt();
            $this->user->updatedAt();
            $this->user->flags(0);

            if (!$this->user->save()) {
                $transaction->rollBack();
                return false;
            }

            if ($isConfirmation === true) {
                $this->insertToken();
            }

            $this->generateAvatar();

            $this->profile->link('user', $this->user);

            $transaction->commit();

            $result = true;
        } catch (Exception $e) {
            $transaction->rollBack();
            $this->logger->log(LogLevel::WARNING, $e->getMessage());
            throw $e;
        }

        return $result;
    }

    /**
     * Generate password.
     *
     * generates user-friendly random password containing at least one lower case letter, one uppercase letter and one
     * digit. The remaining characters in the password are chosen at random from those three sets
     *
     * @param int $length
     *
     * @return string
     *
     * {@see https://gist.github.com/tylerhall/521810}
     *
     * @psalm-suppress MixedOperand, PossiblyInvalidArrayOffset
     */
    private function generate(int $length): string
    {
        $sets = [
            'abcdefghjkmnpqrstuvwxyz',
            'ABCDEFGHJKMNPQRSTUVWXYZ',
            '23456789',
        ];
        $all = '';
        $password = '';
        foreach ($sets as $set) {
            $password .= $set[array_rand(str_split($set))];
            $all .= $set;
        }

        $all = str_split($all);
        for ($i = 0; $i < $length - count($sets); $i++) {
            $password .= $all[array_rand($all)];
        }

        return str_shuffle($password);
    }

    private function generateAvatar(): void
    {
        $avatar = $this->avatar->name($this->user->getUsername())
            ->length(2)
            ->fontSize(0.5)
            ->size(28)
            ->background('#1e6887')
            ->color('#fff')
            ->rounded()
            ->generateSvg()
            ->toXMLString();

        FileHelper::ensureDirectory($this->aliases->get('@avatars'));

        file_put_contents($this->aliases->get('@avatars') . '/' . $this->user->getId() . '.svg', $avatar);
    }

    private function insertToken(): void
    {
        $this->token->deleteAll(['user_id' => $this->token->getUserId()]);

        $this->token->setAttribute('type', Token::TYPE_CONFIRMATION);
        $this->token->setAttribute('created_at', time());
        $this->token->setAttribute('code', Random::string());

        $this->token->link('user', $this->user);
    }

    private function userQuery(): ActiveQueryInterface
    {
        return $this->activeRecordFactory->createQueryTo(User::class);
    }
}
