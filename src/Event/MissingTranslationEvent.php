<?php

declare(strict_types=1);

namespace Yiisoft\Translator\Event;

final class MissingTranslationEvent
{
    private string $category;
    private string $language;
    private string $message;

    public function __construct(string $category, string $language, string $message)
    {
        $this->category = $category;
        $this->language = $language;
        $this->message = $message;
    }

    public function getCategory(): string
    {
        return $this->category;
    }

    public function getLanguage(): string
    {
        return $this->language;
    }

    public function getMessage(): string
    {
        return $this->message;
    }
}
