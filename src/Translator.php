<?php

declare(strict_types=1);

namespace Yiisoft\Translator;

use Psr\EventDispatcher\EventDispatcherInterface;
use RuntimeException;
use Yiisoft\I18n\Locale;
use Yiisoft\Translator\Event\MissingTranslationCategoryEvent;
use Yiisoft\Translator\Event\MissingTranslationEvent;

/**
 * Translator translates a message into the specified language.
 */
final class Translator implements TranslatorInterface
{
    private string $defaultCategory = 'app';
    private string $locale;
    private ?string $fallbackLocale;
    private ?EventDispatcherInterface $eventDispatcher;

    /**
     * @var CategorySource[][] Array of category message sources indexed by category names.
     */
    private array $categorySources = [];

    /**
     * @param string $locale Default locale to use if locale is not specified explicitly.
     * @param string|null $fallbackLocale Locale to use if message for the locale specified was not found. Null for none.
     * @param EventDispatcherInterface|null $eventDispatcher Event dispatcher for translation events. Null for none.
     */
    public function __construct(
        string $locale,
        ?string $fallbackLocale = null,
        ?EventDispatcherInterface $eventDispatcher = null
    ) {
        $this->locale = $locale;
        $this->fallbackLocale = $fallbackLocale;
        $this->eventDispatcher = $eventDispatcher;
    }

    public function addCategorySource(CategorySource $category): void
    {
        if (isset($this->categorySources[$category->getName()])) {
            $this->categorySources[$category->getName()][] = $category;
        } else {
            $this->categorySources[$category->getName()] = [$category];
        }
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

        if (empty($this->categorySources[$category])) {
            if ($this->eventDispatcher !== null) {
                $this->eventDispatcher->dispatch(new MissingTranslationCategoryEvent($category));
            }
            return $id;
        }

        return $this->translateUsingCategorySources($id, $parameters, $category, $locale);
    }

    /**
     * @psalm-immutable
     */
    public function withCategory(string $category): self
    {
        if (!isset($this->categorySources[$category])) {
            throw new RuntimeException('Category with name "' . $category . '" does not exist.');
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

    private function translateUsingCategorySources(
        string $id,
        array $parameters,
        string $category,
        string $locale
    ): string {
        $sourceCategory = end($this->categorySources[$category]);
        do {
            $message = $sourceCategory->getMessage($id, $locale, $parameters);

            if ($message !== null) {
                return $sourceCategory->format($message, $parameters, $locale);
            }

            if ($this->eventDispatcher !== null) {
                $this->eventDispatcher->dispatch(new MissingTranslationEvent($sourceCategory->getName(), $locale, $id));
            }
        } while (($sourceCategory = prev($this->categorySources[$category])) !== false);

        $localeObject = new Locale($locale);
        $fallback = $localeObject->fallbackLocale();

        if ($fallback->asString() !== $localeObject->asString()) {
            return $this->translateUsingCategorySources($id, $parameters, $category, $fallback->asString());
        }

        if (!empty($this->fallbackLocale)) {
            $fallbackLocaleObject = (new Locale($this->fallbackLocale))->fallbackLocale();
            if ($fallbackLocaleObject->asString() !== $localeObject->asString()) {
                return $this->translateUsingCategorySources($id, $parameters, $category, $fallbackLocaleObject->asString());
            }
        }

        $categorySource = end($this->categorySources[$category]);
        return $categorySource->format($id, $parameters, $locale);
    }
}
