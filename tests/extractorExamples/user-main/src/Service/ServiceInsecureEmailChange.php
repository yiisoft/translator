<?php

declare(strict_types=1);

namespace Yii\Extension\User\Service;

use Yii\Extension\Service\ServiceFlashMessage;
use Yii\Extension\User\ActiveRecord\User;
use Yiisoft\Translator\TranslatorInterface;

final class ServiceInsecureEmailChange
{
    private ServiceFlashMessage $serviceFlashMessage;
    private TranslatorInterface $translator;

    public function __construct(ServiceFlashMessage $serviceFlashMessage, TranslatorInterface $translator)
    {
        $this->serviceFlashMessage = $serviceFlashMessage;
        $this->translator = $translator;
    }

    public function run(string $email, User $user): void
    {
        $user->email($email);

        $result = (bool) $user->update();

        if ($result) {
            $this->serviceFlashMessage->run(
                'success',
                $this->translator->translate('System Notification', [], 'user'),
                $this->translator->translate('Your email address has been changed', [], 'user'),
            );
        }
    }
}
