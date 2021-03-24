<?php

declare(strict_types=1);

namespace Yii\Extension\User\Service;

use Yii\Extension\Service\ServiceFlashMessage;
use Yii\Extension\User\ActiveRecord\Token;
use Yii\Extension\User\ActiveRecord\User;
use Yii\Extension\User\Repository\RepositoryToken;
use Yii\Extension\User\Repository\RepositoryUser;
use Yii\Extension\User\Settings\RepositorySetting;
use Yiisoft\Translator\TranslatorInterface;

final class ServiceAttemptEmailChange
{
    private RepositorySetting $repositorySetting;
    private RepositoryToken $repositoryToken;
    private RepositoryUser $repositoryUser;
    private ServiceFlashMessage $serviceFlashMessage;
    private TranslatorInterface $translator;

    public function __construct(
        RepositorySetting $repositorySetting,
        RepositoryToken $repositoryToken,
        RepositoryUser $repositoryUser,
        ServiceFlashMessage $serviceFlashMessage,
        TranslatorInterface $translator
    ) {
        $this->repositorySetting = $repositorySetting;
        $this->repositoryToken = $repositoryToken;
        $this->repositoryUser = $repositoryUser;
        $this->serviceFlashMessage = $serviceFlashMessage;
        $this->translator = $translator;
    }

    public function run(string $id, string $code, User $user): bool
    {
        $result = true;

        $emailChangeStrategy = $this->repositorySetting->getEmailChangeStrategy();
        $tokenConfirmWithin = $this->repositorySetting->getTokenConfirmWithin();
        $tokenRecoverWithin = $this->repositorySetting->getTokenRecoverWithin();

        /** @var Token|null $token */
        $token = $this->repositoryToken->findToken([
            'user_id' => $user->getId(),
            'code' => $code,
        ])->andWhere(['IN', 'type', [Token::TYPE_CONFIRM_NEW_EMAIL, Token::TYPE_CONFIRM_OLD_EMAIL]])->one();

        if ($token === null || $token->isExpired($tokenConfirmWithin, $tokenRecoverWithin)) {
            $this->serviceFlashMessage->run(
                'danger',
                $this->translator->translate('System Notification', [], 'user'),
                $this->translator->translate('Your confirmation token is invalid or expired', [], 'user'),
            );

            $result = false;
        }

        if ($token !== null && $this->repositoryUser->findUserByEmail($user->getUnconfirmedEmail()) === null) {
            $token->delete();

            if ($emailChangeStrategy === User::STRATEGY_SECURE) {
                if ($token->getType() === Token::TYPE_CONFIRM_NEW_EMAIL) {
                    $user->flags |= User::NEW_EMAIL_CONFIRMED;
                }

                if ($token->getType() === Token::TYPE_CONFIRM_OLD_EMAIL) {
                    $user->flags |= User::OLD_EMAIL_CONFIRMED;
                }
            }

            if (
                $emailChangeStrategy === User::STRATEGY_DEFAULT ||
                ($user->flags & User::NEW_EMAIL_CONFIRMED) && ($user->flags & User::OLD_EMAIL_CONFIRMED)
            ) {
                $user->email($user->getUnconfirmedEmail());
                $user->unconfirmedEmail(null);

                $this->serviceFlashMessage->run(
                    'success',
                    $this->translator->translate('System Notification', [], 'user'),
                    $this->translator->translate('Your email address has been changed', [], 'user'),
                );
            }

            $result = $user->save();
        }

        return $result;
    }
}
