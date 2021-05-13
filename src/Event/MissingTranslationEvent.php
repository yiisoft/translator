<?php

declare(strict_types=1);

namespace Yiisoft\Translator\Event;

/**
 * The event is thrown when translation is missing.
 */
final class MissingTranslationEvent
{
    private string $category;
    private string $language;
    private string $message;

    /**
     * @param string $category Category of the missing translation.
     * @param string $language Language of the missing translation.
     * @param string $message Message of the missing translation.
     */
    public function __construct(string $category, string $language, string $message)
    {
        $this->category = $category;
        $this->language = $language;
        $this->message = $message;
    }

    /**
     * @return string Category of the missing translation.
     */
    public function getCategory(): string
    {
        return $this->category;
    }

    /**
     * @return string Language of the missing translation.
     */
    public function getLanguage(): string
    {
        return $this->language;
    }

    /**
     * @return string Message of the missing translation.
     */
    public function getMessage(): string
    {
        return $this->message;
    }
}
