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
use Yii\Extension\User\Event\AfterRequest;
use Yii\Extension\User\Form\FormRequest;
use Yii\Extension\User\Repository\RepositoryToken;
use Yii\Extension\User\Service\MailerUser;
use Yii\Extension\User\Settings\RepositorySetting;
use Yiisoft\Router\UrlGeneratorInterface;
use Yiisoft\Translator\TranslatorInterface;
use Yiisoft\Validator\ValidatorInterface;
use Yiisoft\Yii\View\ViewRenderer;

final class Request
{
    public function run(
        AfterRequest $afterRequest,
        EventDispatcherInterface $eventDispatcher,
        FormRequest $formRequest,
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

        if ($method === 'POST' && $formRequest->load($body) && $validator->validate($formRequest)->isValid()) {
            $email = $formRequest->getEmail();
            $userId = $formRequest->getUserId();
            $username = $formRequest->getUsername();

            $repositoryToken->register($userId, Token::TYPE_RECOVERY);

            /** @var Token $token */
            $token = $repositoryToken->findTokenByCondition(['user_id' => $userId, 'type' => Token::TYPE_RECOVERY]);

            $params = [
                'username' => $username,
                'url' => $urlGenerator->generateAbsolute(
                    $token->toUrl(),
                    ['id' => $token->getUserId(), 'code' => $token->getCode()]
                ),
            ];

            if ($mailerUser->sendRecoveryMessage($email, $params)) {
                $serviceFlashMessage->run(
                    'success',
                    $translator->translate('System Notification', [], 'user'),
                    $translator->translate('Please check your email to change your password', [], 'user'),
                );
            }

            $eventDispatcher->dispatch($afterRequest);

            return $serviceUrl->run('login');
        }

        if ($repositorySetting->isPasswordRecovery()) {
            return $viewRenderer
                ->withViewPath('@user-view-views')
                ->render('/recovery/request', ['body' => $body, 'data' => $formRequest]);
        }

        return $requestHandler->handle($serverRequest);
    }
}
