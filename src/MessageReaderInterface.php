<?php

declare(strict_types=1);

namespace Yiisoft\Translator;

interface MessageReaderInterface
{
    public function getMessage(string $id, string $category, string $locale, array $parameters = []): string;
}
