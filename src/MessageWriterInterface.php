<?php

declare(strict_types=1);

namespace Yiisoft\Translator;

use InvalidArgumentException;

/**
 * Message writer writes a set of messages for a specified category and locale.
 */
interface MessageWriterInterface
{
    /**
     * Writes a set of messages for a specified category and locale.
     *
     * @param string $category Category to write messages for.
     * @param string $locale Locale to write messages for.
     * @param MessageInterface[] $messages A set of messages to write.
     *
     * @throws InvalidArgumentException If messages array contains non {@see MessageInterface} items.
     */
    public function write(string $category, string $locale, array $messages): void;
}
