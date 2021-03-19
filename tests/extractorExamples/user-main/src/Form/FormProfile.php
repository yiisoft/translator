<?php

declare(strict_types=1);

namespace Yii\Extension\User\Form;

use DateTimeZone;
use Yiisoft\Form\FormModel;
use Yiisoft\Translator\TranslatorInterface;
use Yiisoft\Validator\Rule\Email;
use Yiisoft\Validator\Rule\InRange;
use Yiisoft\Validator\Rule\Url;

final class FormProfile extends FormModel
{
    private string $name = '';
    private string $publicEmail = '';
    private string $location = '';
    private string $website = '';
    private string $bio = '';
    private string $timezone = '';
    private TranslatorInterface $translator;

    public function __construct(
        TranslatorInterface $translator
    ) {
        $this->translator = $translator;

        parent::__construct();
    }

    public function getAttributeLabels(): array
    {
        return [
            'name' => $this->translator->translate('Name', [], 'user'),
            'publicEmail' => $this->translator->translate('Public email', [], 'user'),
            'location' => $this->translator->translate('Location', [], 'user'),
            'website' => $this->translator->translate('Website', [], 'user'),
            'bio' => $this->translator->translate('Bio', [], 'user'),
            'timezone' => $this->translator->translate('Time zone', [], 'user'),
        ];
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function name(string $value): void
    {
        $this->name = $value;
    }

    public function getPublicEmail(): string
    {
        return $this->publicEmail;
    }

    public function publicEmail(string $value): void
    {
        $this->publicEmail = $value;
    }

    public function getLocation(): string
    {
        return $this->location;
    }

    public function location(string $value): void
    {
        $this->location = $value;
    }

    public function getWebsite(): string
    {
        return $this->website;
    }

    public function website(string $value): void
    {
        $this->website = $value;
    }

    public function getBio(): string
    {
        return $this->bio;
    }

    public function bio(string $value): void
    {
        $this->bio = $value;
    }

    public function getTimezone(): string
    {
        return $this->timezone;
    }

    public function timezone(string $value): void
    {
        $this->timezone = $value;
    }

    public function getRules(): array
    {
        $listlistIdentifiers = DateTimeZone::listIdentifiers();
        $timeZoneIdentifiers = is_array($listlistIdentifiers) ? $listlistIdentifiers : [];
        $email = new Email();
        $inRange = new InRange($timeZoneIdentifiers);
        $url = new Url();

        return [
            'publicEmail' => [
                $email
                    ->message($this->translator->translate('This value is not a valid email address', [], 'user'))
                    ->skipOnEmpty(true),
            ],
            'website' => [
                $url
                    ->message($this->translator->translate('This value is not a valid URL', [], 'user'))
                    ->skipOnEmpty(true),
            ],
            'timezone' => [
                $inRange->message($this->translator->translate('This value is invalid', [], 'user')),
            ],
        ];
    }
}
