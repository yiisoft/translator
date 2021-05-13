<?php

declare(strict_types=1);

namespace Yiisoft\Translator\Event;

/**
 * The event is thrown when translation category is missing.
 */
final class MissingTranslationCategoryEvent
{
    private string $category;

    /**
     * @param string $category Category that is missing.
     */
    public function __construct(string $category)
    {
        $this->category = $category;
    }

    /**
     * @return string Category that is missing.
     */
    public function getCategory(): string
    {
        return $this->category;
    }
}
