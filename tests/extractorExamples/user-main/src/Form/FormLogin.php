<?php

declare(strict_types=1);

namespace Yii\Extension\User\Form;

use Yii\Extension\User\ActiveRecord\User;
use Yii\Extension\User\Repository\RepositoryUser;
use Yii\Extension\User\Settings\RepositorySetting;
use Yiisoft\Form\FormModel;
use Yiisoft\Security\PasswordHasher;
use Yiisoft\Translator\TranslatorInterface;
use Yiisoft\User\CurrentUser as Identity;
use Yiisoft\Validator\Result;
use Yiisoft\Validator\Rule\Boolean;
use Yiisoft\Validator\Rule\Required;

use function strtolower;

final class FormLogin extends FormModel
{
    private string $login = '';
    private string $password = '';
    private bool $remember = false;
    private string $ip = '';
    private int $lastLogout = 0;
    private Identity $identity;
    private RepositoryUser $repositoryUser;
    private RepositorySetting $repositorySetting;
    private TranslatorInterface $translator;

    public function __construct(
        Identity $identity,
        RepositoryUser $repositoryUser,
        RepositorySetting $repositorySetting,
        TranslatorInterface $translator
    ) {
        $this->identity = $identity;
        $this->repositoryUser = $repositoryUser;
        $this->repositorySetting = $repositorySetting;
        $this->translator = $translator;

        parent::__construct();
    }

    public function getAttributeLabels(): array
    {
        return [
            'login' => $this->translator->translate('Username', [], 'user'),
            'password' => $this->translator->translate('Password', [], 'user'),
            'remember' => $this->translator->translate('Remember me', [], 'user'),
        ];
    }

    public function getFormName(): string
    {
        return 'Login';
    }

    public function ip(string $value): void
    {
        $this->ip = $value;
    }

    public function getLastLogout(): int
    {
        return $this->lastLogout;
    }

    public function getRules(): array
    {
        $boolean = new Boolean();
        $required = new Required();

        return [
            'login' => [$required->message($this->translator->translate('Value cannot be blank', [], 'user'))],
            'password' => $this->passwordRules(),
            'remember' => [
                $boolean->message($this->translator->translate('The value must be either "1" or "0"', [], 'user')),
            ],
        ];
    }

    private function passwordRules(): array
    {
        $passwordHasher = new PasswordHasher();
        $required = new Required();

        return [
            $required->message($this->translator->translate('Value cannot be blank', [], 'user')),

            function () use ($passwordHasher): Result {
                if (!$this->repositorySetting->getUserNameCaseSensitive()) {
                    $this->login = strtolower($this->login);
                }

                /** @var User|null $user */
                $user = $this->repositoryUser->findUserByUsernameOrEmail($this->login);

                $result = new Result();

                if ($user === null) {
                    $result->addError($this->translator->translate('Invalid login or password', [], 'user'));
                }

                if ($user !== null && $user->isBlocked()) {
                    $result->addError(
                        $this->translator->translate('Your user has been blocked, contact an administrator', [], 'user')
                    );
                }

                if ($user !== null && !$user->isConfirmed()) {
                    $result->addError(
                        $this->translator->translate('Please check your email to activate your account', [], 'user')
                    );
                }

                if ($user !== null && !$passwordHasher->validate($this->password, $user->getPasswordHash())) {
                    $result->addError($this->translator->translate('Invalid login or password', [], 'user'));
                }

                if ($result->isValid() && $user !== null) {
                    $this->lastLogout = $user->getLastLogout();
                    $user->updateAttributes(['ip_last_login' => $this->ip, 'last_login_at' => time()]);
                    $this->identity->login($user);
                }

                return $result;
            },
        ];
    }
}
