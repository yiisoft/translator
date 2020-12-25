<?php

declare(strict_types=1);

namespace Yiisoft\Translator;

/**
 * Represents a message.
 */
interface MessageInterface
{
    /**
     * @return string Translation string.
     */
    public function translation(): string;

    /**
     * @return string[] Extra metadata in `'key' => 'value'` format.
     */
    public function meta(): array;
}
