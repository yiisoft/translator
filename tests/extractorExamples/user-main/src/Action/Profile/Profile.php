<?php

declare(strict_types=1);

namespace Yii\Extension\User\Action\Profile;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Yii\Extension\Service\ServiceFlashMessage;
use Yii\Extension\User\Form\FormProfile;
use Yii\Extension\User\Repository\RepositoryProfile;
use Yiisoft\Translator\TranslatorInterface;
use Yiisoft\User\CurrentUser;
use Yiisoft\Validator\ValidatorInterface;
use Yiisoft\Yii\View\ViewRenderer;

final class Profile
{
    public function run(
        FormProfile $formProfile,
        RepositoryProfile $repositoryProfile,
        ServerRequestInterface $serverRequest,
        ServiceFlashMessage $serviceFlashMessage,
        TranslatorInterface $translator,
        CurrentUser $user,
        ValidatorInterface $validator,
        ViewRenderer $viewRenderer
    ): ResponseInterface {
        /** @var array $body */
        $body = $serverRequest->getParsedBody();
        $method = $serverRequest->getMethod();

        $id = $user->getId();

        if ($id !== null) {
            $repositoryProfile->loadData($id, $formProfile);
        }

        if (
            $method === 'POST' &&
            $id !== null &&
            $formProfile->load($body) &&
            $validator->validate($formProfile)->isValid() &&
            $repositoryProfile->update($id, $formProfile)
        ) {
            $serviceFlashMessage->run(
                'success',
                $translator->translate('System Notification', [], 'user'),
                $translator->translate('Your data has been saved', [], 'user'),
            );
        }

        return $viewRenderer
            ->withViewPath('@user-view-views')
            ->render('profile/profile', ['body' => $body, 'data' => $formProfile]);
    }
}
