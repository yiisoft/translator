<?php

declare(strict_types=1);

namespace Yiisoft\Translator;

interface MessageWriterInterface
{
    public function write(string $category, string $locale, array $messages): void;
}
