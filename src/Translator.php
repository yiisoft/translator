<?php

declare(strict_types=1);

namespace Yiisoft\Translator;

use Psr\EventDispatcher\EventDispatcherInterface;
use Yiisoft\I18n\Locale;
use Yiisoft\Translator\Event\MissingTranslationCategoryEvent;
use Yiisoft\Translator\Event\MissingTranslationEvent;

/**
 * Translator translates a message into the specified language.
 */
class Translator implements TranslatorInterface
{
    private string $defaultCategory;
    private string $locale;
    private EventDispatcherInterface $eventDispatcher;
    private ?string $fallbackLocale;
    /**
     * @var Category[]
     */
    private array $categories = [];

    /**
     * @param Category $defaultCategory Default category to use if category is not specified explicitly.
     * @param string $locale Default locale to use if locale is not specified explicitly.
     * @param EventDispatcherInterface $eventDispatcher Event dispatcher for translation events.
     * @param string|null $fallbackLocale Locale to use if message for the locale specified was not found. Null for none.
     */
    public function __construct(
        Category $defaultCategory,
        string $locale,
        EventDispatcherInterface $eventDispatcher,
        string $fallbackLocale = null
    ) {
        $this->defaultCategory = $defaultCategory->getName();
        $this->eventDispatcher = $eventDispatcher;
        $this->locale = $locale;
        $this->fallbackLocale = $fallbackLocale;

        $this->addCategorySource($defaultCategory);
    }

    /**
     * @param Category $category Add category.
     */
    public function addCategorySource(Category $category): void
    {
        $this->categories[$category->getName()] = $category;
    }

    /**
     * Set the default locale.
     *
     * @param string $locale
     */
    public function setLocale(string $locale): void
    {
        $this->locale = $locale;
    }

    /**
     * @return string Default locale.
     */
    public function getLocale(): string
    {
        return $this->locale;
    }

    public function translate(
        string $id,
        array $parameters = [],
        string $category = null,
        string $locale = null
    ): string {
        $locale = $locale ?? $this->locale;

        $category = $category ?? $this->defaultCategory;

        if (empty($this->categories[$category])) {
            $this->eventDispatcher->dispatch(new MissingTranslationCategoryEvent($category));
            return $id;
        }

        $sourceCategory = $this->categories[$category];
        $message = $sourceCategory->getMessage($id, $locale, $parameters);

        if ($message === null) {
            $this->eventDispatcher->dispatch(new MissingTranslationEvent($sourceCategory->getName(), $locale, $id));

            $localeObject = new Locale($locale);
            $fallback = $localeObject->fallbackLocale();

            if ($fallback->asString() !== $localeObject->asString()) {
                return $this->translate($id, $parameters, $category, $fallback->asString());
            }

            if (!empty($this->fallbackLocale)) {
                $fallbackLocaleObject = (new Locale($this->fallbackLocale))->fallbackLocale();
                if ($fallbackLocaleObject->asString() !== $localeObject->asString()) {
                    return $this->translate($id, $parameters, $category, $fallbackLocaleObject->asString());
                }
            }

            $message = $id;
        }

        return $sourceCategory->format($message, $parameters, $locale);
    }
}
