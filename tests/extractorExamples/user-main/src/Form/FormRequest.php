<?php

declare(strict_types=1);

namespace Yii\Extension\User\Form;

use Yii\Extension\User\ActiveRecord\Token;
use Yii\Extension\User\ActiveRecord\User;
use Yii\Extension\User\Repository\RepositoryUser;
use Yiisoft\Form\FormModel;
use Yiisoft\Translator\TranslatorInterface;
use Yiisoft\Validator\Result;
use Yiisoft\Validator\Rule\Email;
use Yiisoft\Validator\Rule\Required;

use function strtolower;

final class FormRequest extends FormModel
{
    private string $email = '';
    private string $userId = '';
    private string $username = '';
    private RepositoryUser $repositoryUser;
    private TranslatorInterface $translator;
    private Token $token;

    public function __construct(
        Token $token,
        RepositoryUser $repositoryUser,
        TranslatorInterface $translator
    ) {
        $this->token = $token;
        $this->repositoryUser = $repositoryUser;
        $this->translator = $translator;

        parent::__construct();
    }

    public function getAttributeLabels(): array
    {
        return [
            'email' => $this->translator->translate('Email', [], 'user'),
        ];
    }

    public function getFormName(): string
    {
        return 'Request';
    }

    public function getEmail(): string
    {
        return strtolower($this->email);
    }

    public function getRules(): array
    {
        return [
            'email' => $this->emailRules(),
        ];
    }

    public function getUserId(): string
    {
        return $this->userId;
    }

    public function getUsername(): string
    {
        return $this->username;
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

                /** @var User|null $user */
                $user = $this->repositoryUser->findUserByUsernameOrEmail($this->email);

                if ($user === null) {
                    $result->addError(
                        $this->translator->translate(
                            'Thank you. If said email is registered, you will get a password reset',
                            [],
                            'user',
                        )
                    );
                }

                if ($user !== null && !$user->isConfirmed()) {
                    $result->addError($this->translator->translate('Inactive user', [], 'user'));
                }

                if ($result->isValid() && $user !== null) {
                    $this->userId = $user->getId();
                    $this->username = $user->getUsername();
                }

                return $result;
            },
        ];
    }
}
