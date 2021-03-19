<?php

declare(strict_types=1);

namespace Yii\Extension\User\Service;

use Yii\Extension\Service\ServiceFlashMessage;
use Yii\Extension\User\ActiveRecord\Token;
use Yii\Extension\User\ActiveRecord\User;
use Yii\Extension\User\Repository\RepositoryToken;
use Yiisoft\Router\UrlGeneratorInterface;
use Yiisoft\Translator\TranslatorInterface;

final class ServiceSecureEmailChange
{
    private MailerUser $mailerUser;
    private RepositoryToken $repositoryToken;
    private ServiceFlashMessage $serviceFlashMessage;
    private TranslatorInterface $translator;
    private UrlGeneratorInterface $urlGenerator;

    public function __construct(
        MailerUser $mailerUser,
        RepositoryToken $repositoryToken,
        ServiceFlashMessage $serviceFlashMessage,
        TranslatorInterface $translator,
        UrlGeneratorInterface $urlGenerator
    ) {
        $this->mailerUser = $mailerUser;
        $this->repositoryToken = $repositoryToken;
        $this->serviceFlashMessage = $serviceFlashMessage;
        $this->translator = $translator;
        $this->urlGenerator = $urlGenerator;
    }

    public function run(User $user): void
    {
        $result = $this->repositoryToken->register($user->getId(), Token::TYPE_CONFIRM_OLD_EMAIL);
        $email = $user->getEmail();

        /** @var Token|null $token */
        $token = $this->repositoryToken->findTokenByCondition(
            ['user_id' => $user->getId(), 'type' => Token::TYPE_CONFIRM_OLD_EMAIL]
        );

        if ($result && $token !== null) {
            $params = [
                'username' => $user->getUsername(),
                'url' => $this->urlGenerator->generateAbsolute(
                    $token->toUrl(),
                    ['id' => $token->getUserId(), 'code' => $token->getCode()]
                ),
            ];

            if ($this->mailerUser->sendReconfirmationMessage($email, $params)) {
                $this->serviceFlashMessage->run(
                    'info',
                    $this->translator->translate('System Notification', [], 'user'),
                    $this->translator->translate(
                        'We have sent confirmation links to both old email: {email} and new email: {newEmail} addresses.' .
                        ' You must click both links to complete your request',
                        ['email' => $user->getEmail(), 'newEmail' => $user->getUnconfirmedEmail()],
                        'user',
                    ),
                );
            }

            // unset flags if they exist
            $user->flags &= ~User::NEW_EMAIL_CONFIRMED;
            $user->flags &= ~User::OLD_EMAIL_CONFIRMED;

            $user->update();
        }
    }
}
