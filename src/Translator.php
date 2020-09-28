<?php

declare(strict_types=1);

namespace Yiisoft\Translator;

use Psr\EventDispatcher\EventDispatcherInterface;
use Yiisoft\Translator\Event\MissingTranslationEvent;

class Translator implements TranslatorInterface
{
    private string $defaultLocale = '';
    private string $defaultCategory = '';
    private EventDispatcherInterface $eventDispatcher;

    /**
     * @var Category[]
     */
    private array $categories = [];

    public function __construct(
        string $defaultCategory,
        string $defaultLocale,
        MessageReaderInterface $reader,
        MessageFormatterInterface $formatter,
        EventDispatcherInterface $eventDispatcher
    ) {
        $this->defaultCategory = $defaultCategory;
        $this->defaultLocale = $defaultLocale;
        $this->eventDispatcher = $eventDispatcher;
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
        if ($message === null) {
            $missingTranslation = new MissingTranslationEvent($category, $locale, $id);
            $this->eventDispatcher->dispatch($missingTranslation);

            $localeObject = new Locale($locale);
            $fallback = $localeObject->fallbackLocale();

            if ($fallback->asString() !== $localeObject->asString()) {
                $message = $this->categories[$category]->getReader()->getMessage($id, $category, $fallback->asString(), $parameters);
            }
            if ($message === null) {
                $message = $id;
            }
        }

        $message = $this->categories[$category]->getFormatter()->format($message, $parameters, $locale);

        return $message;
    }
}
