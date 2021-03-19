<?php

declare(strict_types=1);

namespace Yii\Extension\User\Action\Registration;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Yii\Extension\Service\ServiceFlashMessage;
use Yii\Extension\Service\ServiceUrl;
use Yii\Extension\User\ActiveRecord\Token;
use Yii\Extension\User\ActiveRecord\User;
use Yii\Extension\User\Repository\RepositoryToken;
use Yii\Extension\User\Repository\RepositoryUser;
use Yii\Extension\User\Settings\RepositorySetting;
use Yiisoft\Translator\TranslatorInterface;
use Yiisoft\User\CurrentUser as Identity;

final class Confirm
{
    public function run(
        Identity $identity,
        ServerRequestInterface $serverRequest,
        RequestHandlerInterface $requestHandler,
        RepositorySetting $repositorySetting,
        RepositoryToken $repositoryToken,
        RepositoryUser $repositoryUser,
        ServiceFlashMessage $serviceFlashMessage,
        ServiceUrl $serviceUrl,
        TranslatorInterface $translator
    ): ResponseInterface {
        /** @var string|null $id */
        $id = $serverRequest->getAttribute('id');

        /** @var string|null $code */
        $code = $serverRequest->getAttribute('code');

        /** @var string $ip */
        $ip = $serverRequest->getServerParams()['REMOTE_ADDR'];

        if ($id === null || ($user = $repositoryUser->findUserById($id)) === null || $code === null) {
            return $requestHandler->handle($serverRequest);
        }

        /**
         * @var Token|null $token
         * @var User $user
         */
        $token = $repositoryToken->findTokenByParams(
            $user->getId(),
            $code,
            Token::TYPE_CONFIRMATION
        );

        if ($token === null || $token->isExpired($repositorySetting->getTokenConfirmWithin())) {
            return $requestHandler->handle($serverRequest);
        }

        if (!$token->isExpired($repositorySetting->getTokenConfirmWithin())) {
            $token->delete();

            $user->updateAttributes([
                'confirmed_at' => time(),
                'ip_last_login' => $ip,
                'last_login_at' => time(),
                'unconfirmed_email' => null,
            ]);

            $identity->login($user);

            $serviceFlashMessage->run(
                'success',
                $translator->translate('System Notification', [], 'user'),
                $translator->translate('Your user has been confirmed', [], 'user'),
            );
        }

        return $serviceUrl->run('home');
    }
}
