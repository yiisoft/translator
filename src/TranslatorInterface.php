<?php

declare(strict_types=1);

namespace Yiisoft\Translator;

/**
 * Translator translates a message into the specified language.
 */
interface TranslatorInterface
{
    /**
     * Translates a message into the specified language.
     *
     * @param string $id The ID of the message to be translated. It can be either artificial ID or the source message.
     * @param array  $parameters An array of parameters for the message.
     * @param string|null $category The message category. Null means default category.
     * @param string|null $locale The target locale. Null means default locale.
     *
     * @return string The translated message or source string ID if translation was not found or is not required.
     */
    public function translate(
        string $id,
        array $parameters = [],
        string $category = null,
        string $locale = null
    ): string;
}
