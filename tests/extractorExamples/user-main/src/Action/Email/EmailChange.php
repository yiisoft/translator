<?php

declare(strict_types=1);

namespace Yii\Extension\User\Action\Email;

use OutOfBoundsException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Yii\Extension\User\ActiveRecord\User;
use Yii\Extension\User\Form\FormEmailChange;
use Yii\Extension\User\Service\ServiceDefaultEmailChange;
use Yii\Extension\User\Service\ServiceInsecureEmailChange;
use Yii\Extension\User\Service\ServiceSecureEmailChange;
use Yii\Extension\User\Settings\RepositorySetting;
use Yiisoft\User\CurrentUser as Identity;
use Yiisoft\Validator\ValidatorInterface;
use Yiisoft\Yii\View\ViewRenderer;

final class EmailChange
{
    public function run(
        FormEmailChange $formEmailChange,
        Identity $identity,
        RepositorySetting $repositorySetting,
        ServerRequestInterface $serverRequest,
        ServiceDefaultEmailChange $serviceDefaultEmailChange,
        ServiceInsecureEmailChange $serviceInsecureEmailChange,
        ServiceSecureEmailChange $serviceSecureEmailChange,
        ValidatorInterface $validator,
        ViewRenderer $viewRenderer
    ): ResponseInterface {
        /** @var array $body */
        $body = $serverRequest->getParsedBody();
        $method = $serverRequest->getMethod();

        if ($method === 'POST' && $formEmailChange->load($body) && $validator->validate($formEmailChange)->isValid()) {
            /** @var User $user */
            $user = $identity->getIdentity();
            $email = $formEmailChange->getEmail();

            if ($email === $user->getEmail() && empty($user->getUnconfirmedEmail())) {
                $user->unconfirmedEmail(null);
            } elseif ($email !== $user->getEmail()) {
                switch ($repositorySetting->getEmailChangeStrategy()) {
                    case User::STRATEGY_INSECURE:
                        $serviceInsecureEmailChange->run($email, $user);
                        break;
                    case User::STRATEGY_DEFAULT:
                        $serviceDefaultEmailChange->run($email, $user);
                        break;
                    case User::STRATEGY_SECURE:
                        $serviceDefaultEmailChange->run($email, $user, false);
                        $serviceSecureEmailChange->run($user);
                        break;
                    default:
                        throw new OutOfBoundsException('Invalid email changing strategy.');
                }
            }
        }

        return $viewRenderer
            ->withViewPath('@user-view-views')
            ->render('email/change', ['body' => $body, 'data' => $formEmailChange]);
    }
}
