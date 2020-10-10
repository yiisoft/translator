<?php

declare(strict_types=1);

namespace Yiisoft\Translator;

interface MessageReaderInterface
{
    /**
     * @return string|null the translated message or null if translation wasn't found
     */
    public function getMessage(string $id, string $locale, array $parameters = []): ?string;
}
