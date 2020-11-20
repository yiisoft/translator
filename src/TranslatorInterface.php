<?php

declare(strict_types=1);

namespace Yiisoft\Translator;

interface TranslatorInterface
{
    /**
     * Translates a message to the specified language.
     *
     * @param string $id the id of the message to be translated. It can be either artificial ID or the source message.
     * @param array  $parameters An array of parameters for the message
     * @param string|null $category the message category
     * @param string|null $locale the target locale
     *
     * @return string the translated message or source string id if translation wasn't found or isn't required
     */
    public function translate(
        string $id,
        array $parameters = [],
        string $category = null,
        string $locale = null
    ): string;
}
