<?php

declare(strict_types=1);

namespace Yii\Extension\User\Action\Recovery;

use Psr\EventDispatcher\EventDispatcherInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Yii\Extension\Service\ServiceFlashMessage;
use Yii\Extension\Service\ServiceUrl;
use Yii\Extension\User\ActiveRecord\Token;
use Yii\Extension\User\Event\AfterResend;
use Yii\Extension\User\Form\FormResend;
use Yii\Extension\User\Repository\RepositoryToken;
use Yii\Extension\User\Service\MailerUser;
use Yii\Extension\User\Settings\RepositorySetting;
use Yiisoft\Router\UrlGeneratorInterface;
use Yiisoft\Translator\TranslatorInterface;
use Yiisoft\Validator\ValidatorInterface;
use Yiisoft\Yii\View\ViewRenderer;

final class Resend
{
    public function run(
        AfterResend $afterResend,
        EventDispatcherInterface $eventDispatcher,
        FormResend $formResend,
        MailerUser $mailerUser,
        RequestHandlerInterface $requestHandler,
        RepositorySetting $repositorySetting,
        RepositoryToken $repositoryToken,
        ServerRequestInterface $serverRequest,
        ServiceFlashMessage $serviceFlashMessage,
        ServiceUrl $serviceUrl,
        TranslatorInterface $translator,
        UrlGeneratorInterface $urlGenerator,
        ValidatorInterface $validator,
        ViewRenderer $viewRenderer
    ): ResponseInterface {
        /** @var array $body */
        $body = $serverRequest->getParsedBody();
        $method = $serverRequest->getMethod();

        if ($method === 'POST' && $formResend->load($body) && $validator->validate($formResend)->isValid()) {
            $email = $formResend->getEmail();
            $userId = $formResend->getUserId();
            $username = $formResend->getUsername();

            /** @var Token $token */
            $token = $repositoryToken->findTokenById($userId);

            $params = [
                'username' => $username,
                'url' => $urlGenerator->generateAbsolute(
                    $token->toUrl(),
                    ['id' => $token->getUserId(), 'code' => $token->getCode()]
                ),
            ];

            if ($mailerUser->sendConfirmationMessage($email, $params)) {
                $serviceFlashMessage->run(
                    'success',
                    $translator->translate('System Notification', [], 'user'),
                    $translator->translate('Please check your email to activate your username', [], 'user'),
                );
            }

            $eventDispatcher->dispatch($afterResend);

            return $serviceUrl->run('login');
        }

        if ($repositorySetting->isConfirmation()) {
            return $viewRenderer
                ->withViewPath('@user-view-views')
                ->render('/recovery/resend', ['body' => $body, 'data' => $formResend]);
        }

        return $requestHandler->handle($serverRequest);
    }
}
