<?php

declare(strict_types=1);

namespace Yiisoft\Translator;

use RuntimeException;

/**
 * ID message reader returns ID as message and don't support receiving all messages.
 */
final class IdMessageReader implements MessageReaderInterface
{
    public function getMessage(string $id, string $category, string $locale, array $parameters = []): string
    {
        return $id;
    }

    public function getMessages(string $category, string $locale): array
    {
        throw new RuntimeException('IdMessageReader do not support receiving all messages.');
    }
}
