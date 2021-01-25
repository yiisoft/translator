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
    private string $defaultCategory = 'app';
    private string $locale;
    private ?EventDispatcherInterface $eventDispatcher;
    private ?string $fallbackLocale;
    /**
     * @var CategorySource[]
     */
    private array $categories = [];

    /**
     * @param string $locale Default locale to use if locale is not specified explicitly.
     * @param CategorySource|null $defaultCategory Default category to use if category is not specified explicitly, or null for use without default category
     * @param string|null $fallbackLocale Locale to use if message for the locale specified was not found. Null for none.
     * @param EventDispatcherInterface|null $eventDispatcher Event dispatcher for translation events. Null for none.
     */
    public function __construct(
        string $locale,
        ?CategorySource $defaultCategory = null,
        ?string $fallbackLocale = null,
        ?EventDispatcherInterface $eventDispatcher = null
    ) {
        $this->eventDispatcher = $eventDispatcher;
        $this->locale = $locale;
        $this->fallbackLocale = $fallbackLocale;
        if ($defaultCategory) {
            $this->defaultCategory = $defaultCategory->getName();
            $this->addCategorySource($defaultCategory);
        }
    }

    public function addCategorySource(CategorySource $category): void
    {
        if (isset($this->categories[$category->getName()])) {
            throw new \RuntimeException('Category "' . $category->getName() . '" already exists.');
        }
        $this->categories[$category->getName()] = $category;
    }

    /**
     * @param CategorySource[] $categories
     */
    public function addCategorySources(array $categories): void
    {
        foreach ($categories as $category) {
            $this->addCategorySource($category);
        }
    }

    public function setLocale(string $locale): void
    {
        $this->locale = $locale;
    }

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
            if ($this->eventDispatcher !== null) {
                $this->eventDispatcher->dispatch(new MissingTranslationCategoryEvent($category));
            }
            return $id;
        }

        $sourceCategory = $this->categories[$category];
        $message = $sourceCategory->getMessage($id, $locale, $parameters);

        if ($message === null) {
            if ($this->eventDispatcher !== null) {
                $this->eventDispatcher->dispatch(new MissingTranslationEvent($sourceCategory->getName(), $locale, $id));
            }

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

    /**
     * @psalm-immutable
     */
    public function withCategory(string $category): self
    {
        if (!isset($this->categories[$category])) {
            throw new \RuntimeException('Category with name "' . $category . '" does not exist.');
        }

        $new = clone $this;
        $new->defaultCategory = $category;
        return $new;
    }

    public function withLocale(string $locale): self
    {
        $new = clone $this;
        $new->setLocale($locale);
        return $new;
    }
}
