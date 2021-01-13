<?php

declare(strict_types=1);

namespace Yiisoft\Translator;

/**
 * Translator translates a message into the specified language.
 */
interface TranslatorInterface
{
    /**
     * @param Category $category Add category.
     */
    public function addCategorySource(Category $category): void;

    /**
     * @param Category[] $categories Adding many categories.
     */
    public function addMultiCategorySource(array $categories): void;

    /**
     * Set the default locale.
     *
     * @param string $locale
     */
    public function setLocale(string $locale): void;

    /**
     * @return string Default locale.
     */
    public function getLocale(): string;

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

    /**
     * Change default category (if exists return new Translator)
     *
     * @param string $category
     *
     * @return TranslatorInterface
     */
    public function withCategory(string $category): TranslatorInterface;

    /**
     * Change locale and return new Translator
     *
     * @param string $locale
     *
     * @return TranslatorInterface
     */
    public function withLocale(string $locale): TranslatorInterface;
}
