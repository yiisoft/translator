<?php

declare(strict_types=1);

namespace Yiisoft\Translator;

class Translator implements TranslatorInterface
{
    private string $defaultLocale = '';

    private string $defaultCategory = '';

    /**
     * @var Category[]
     */
    private array $categories = [];

    public function __construct(string $defaultCategory, string $defaultLocale, MessageReaderInterface $reader, MessageFormatterInterface $formatter)
    {
        $this->defaultCategory = $defaultCategory;
        $this->defaultLocale = $defaultLocale;
        $this->addCategorySource($defaultCategory, $reader, $formatter);
    }

    public function addCategorySource(string $category, MessageReaderInterface $reader, MessageFormatterInterface $formatter): void
    {
        $this->categories[$category] = new Category($reader, $formatter);
    }

    public function translate(string $id, array $parameters = [], string $category = null, string $locale = null): string
    {
        $locale = $locale ?? $this->defaultLocale;
        if (empty($locale)) {
            return $id;
        }

        $category = $category ?? $this->defaultCategory;
        if (empty($category) or empty($this->categories[$category])) {
            return $id;
        }

        $message = $this->categories[$category]->getReader()->getMessage($id, $category, $locale, $parameters);
        $message = $this->categories[$category]->getFormatter()->format($message, $parameters, $locale);

        return $message;
    }
}
