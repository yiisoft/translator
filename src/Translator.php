<?php

declare(strict_types=1);

namespace Yiisoft\Translator;

use Psr\EventDispatcher\EventDispatcherInterface;
use Yiisoft\Translator\Event\MissingTranslationEvent;

class Translator implements TranslatorInterface
{
    private string $defaultCategory;
    private EventDispatcherInterface $eventDispatcher;
    private string $locale;
    private ?string $fallbackLocale;
    /**
     * @var Category[]
     */
    private array $categories = [];

    public function __construct(
        Category $defaultCategory,
        EventDispatcherInterface $eventDispatcher,
        string $locale = 'en-US',
        string $fallbackLocale = null
    ) {
        $this->defaultCategory = $defaultCategory->getName();
        $this->eventDispatcher = $eventDispatcher;
        $this->locale = $locale;
        $this->fallbackLocale = $fallbackLocale;

        $this->addCategorySource($defaultCategory);
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

        $category = $category ?? $this->defaultCategory;
        if (empty($this->categories[$category])) {
            return $id;
        }

        $sourceCategory = $this->categories[$category];
        $message = $sourceCategory->getReader()->getMessage($id, $locale, $parameters);

        if ($message === null) {
            $missingTranslation = new MissingTranslationEvent($sourceCategory->getName(), $locale, $id);
            $this->eventDispatcher->dispatch($missingTranslation);

            $localeObject = new Locale($locale);
            $fallback = $localeObject->fallbackLocale();

            if ($fallback->asString() !== $localeObject->asString()) {
                return $this->translate($id, $parameters, $category, $fallback->asString());
            }

            if (!empty($this->fallbackLocale)) {
                $fallbackLocaleObject = new Locale($this->fallbackLocale);
                $defaultFallback = $fallbackLocaleObject->fallbackLocale();

                if (
                    $fallbackLocaleObject->asString() !== $localeObject->asString() &&
                    $defaultFallback->asString() !== $localeObject->asString()
                ) {
                    return $this->translate($id, $parameters, $category, $fallbackLocaleObject->asString());
                }
            }

            $message = $id;
        }

        return $sourceCategory->getFormatter()->format($message, $parameters, $locale);
    }
}
