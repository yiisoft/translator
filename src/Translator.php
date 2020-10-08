<?php

declare(strict_types=1);

namespace Yiisoft\Translator;

use Psr\EventDispatcher\EventDispatcherInterface;
use Yiisoft\Translator\Event\MissingTranslationEvent;

class Translator implements TranslatorInterface
{
    private Category $defaultCategory;
    private EventDispatcherInterface $eventDispatcher;
    private string $locale = 'en-US';
    private string $fallbackLocale = 'en-US';
    /**
     * @var Category[]
     */
    private array $categories = [];

    public function __construct(
        Category $defaultCategory,
        EventDispatcherInterface $eventDispatcher
    ) {
        $this->defaultCategory = $defaultCategory;
        $this->eventDispatcher = $eventDispatcher;
    }

    public function addCategorySource(Category $category): void
    {
        $this->categories[$category->getName()] = $category;
    }

    /**
     * Sets the current application locale.
     *
     * @param string $locale
     */
    public function setLocale(string $locale): void
    {
        $this->locale = $locale;
    }

    public function getLocale(): string
    {
        return $this->locale;
    }

    public function setFallbackLocale(string $locale): void
    {
        $this->fallbackLocale = $locale;
    }

    public function getFallbackLocale(): ?string
    {
        return $this->fallbackLocale;
    }

    public function translate(
        string $id,
        array $parameters = [],
        string $category = null,
        string $locale = null
    ): string {
        $locale = $locale ?? $this->getLocale();
        $sourceCategory = $this->defaultCategory;

        if (!empty($category) && !empty($this->categories[$category])) {
            $sourceCategory = $this->categories[$category];
        }

        $message = $sourceCategory->getReader()->getMessage($id, $category, $locale, $parameters);

        if ($message === null) {
            $missingTranslation = new MissingTranslationEvent($sourceCategory->getName(), $locale, $id);
            $this->eventDispatcher->dispatch($missingTranslation);

            $localeObject = new Locale($locale);
            $fallback = $localeObject->fallbackLocale();

            if ($fallback->asString() !== $localeObject->asString()) {
                return $this->translate($id, $parameters, $category, $fallback->asString());
            }

            $fallbackLocaleObject = new Locale($this->fallbackLocale);
            $defaultFallback = $fallbackLocaleObject->fallbackLocale();

            if (
                $fallbackLocaleObject->asString() !== $localeObject->asString() &&
                $defaultFallback->asString() !== $localeObject->asString()
            ) {
                return $this->translate($id, $parameters, $category, $fallbackLocaleObject->asString());
            }

            $message = $id;
        }

        return $sourceCategory->getFormatter()->format($message, $parameters, $locale);
    }
}
