<?php

declare(strict_types=1);

namespace Yii\Extension\User\Form;

use Yii\Extension\User\Repository\RepositoryUser;
use Yii\Extension\User\Settings\RepositorySetting;
use Yiisoft\Form\FormModel;
use Yiisoft\Translator\TranslatorInterface;
use Yiisoft\Validator\Result;
use Yiisoft\Validator\Rule\Email;
use Yiisoft\Validator\Rule\HasLength;
use Yiisoft\Validator\Rule\MatchRegularExpression;
use Yiisoft\Validator\Rule\Required;

use function strtolower;

final class FormRegister extends FormModel
{
    private string $email = '';
    private string $username = '';
    private string $password = '';
    private string $ip = '';
    private RepositorySetting $repositorySetting;
    private RepositoryUser $repositoryUser;
    private TranslatorInterface $translator;

    public function __construct(
        RepositoryUser $repositoryUser,
        RepositorySetting $repositorySetting,
        TranslatorInterface $translator
    ) {
        $this->repositoryUser = $repositoryUser;
        $this->repositorySetting = $repositorySetting;
        $this->translator = $translator;

        parent::__construct();
    }

    public function getAttributeLabels(): array
    {
        return [
            'email' => $this->translator->translate('Email', [], 'user'),
            'username' => $this->translator->translate('Username', [], 'user'),
            'password' => $this->translator->translate('Password', [], 'user'),
        ];
    }

    public function getFormName(): string
    {
        return 'Register';
    }

    public function getEmail(): string
    {
        return strtolower($this->email);
    }

    public function getIp(): string
    {
        return $this->ip;
    }

    public function getPassword(): string
    {
        return $this->password;
    }

    public function getUsername(): string
    {
        if (!$this->repositorySetting->getUsernameCaseSensitive()) {
            $this->username = strtolower($this->username);
        }

        return $this->username;
    }

    public function ip(string $value): void
    {
        $this->ip = $value;
    }

    public function password(string $value): void
    {
        $this->password = $value;
    }

    public function getRules(): array
    {
        return [
            'email' => $this->emailRules(),
            'username' => $this->usernameRules(),
            'password' => $this->passwordRules(),
        ];
    }

    private function emailRules(): array
    {
        $email = new Email();
        $required = new Required();

        return [
            $required->message($this->translator->translate('Value cannot be blank', [], 'user')),
            $email->message($this->translator->translate('This value is not a valid email address', [], 'user')),

            function (): Result {
                $result = new Result();

                if ($this->repositoryUser->findUserByUsernameOrEmail($this->email)) {
                    $result->addError($this->translator->translate('Email already registered', [], 'user'));
                }

                return $result;
            },
        ];
    }

    private function usernameRules(): array
    {
        $hasLength = new HasLength();
        $required = new Required();
        $matchRegularExpression = new MatchRegularExpression($this->repositorySetting->getUsernameRegExp());

        return [
            $required->message($this->translator->translate('Value cannot be blank', [], 'user')),
            $hasLength->min(3)->max(255)->tooShortMessage(
                $this->translator->translate('Username should contain at least 3 characters', [], 'user'),
            ),
            $matchRegularExpression->message($this->translator->translate('This value is invalid', [], 'user')),

            function (): Result {
                $result = new Result();

                if ($this->repositoryUser->findUserByUsernameOrEmail($this->username)) {
                    $result->addError($this->translator->translate('Username already registered', [], 'user'));
                }

                return $result;
            },
        ];
    }

    private function passwordRules(): array
    {
        $hasLength = new HasLength();
        $required = new Required();
        $result = [];

        if ($this->repositorySetting->isGeneratingPassword() === false) {
            $result = [
                $required->message($this->translator->translate('Value cannot be blank', [], 'user')),
                $hasLength->min(6)->max(72)->tooShortMessage(
                    $this->translator->translate(
                        'Password should contain at least 6 characters',
                        [],
                        'user'
                    )
                ),
            ];
        }

        return $result;
    }
}
