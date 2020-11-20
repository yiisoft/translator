<?php

declare(strict_types=1);

namespace Yiisoft\Translator\Event;

final class MissingTranslationCategoryEvent
{
    private string $category;

    public function __construct(string $category)
    {
        $this->category = $category;
    }

    public function getCategory(): string
    {
        return $this->category;
    }
}
