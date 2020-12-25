<?php

declare(strict_types=1);

namespace Yiisoft\Translator;

/**
 * Represents a message.
 */
final class Message implements MessageInterface
{
    private string $translation;
    private array $meta;

    /**
     * @param string $translation Translation string.
     * @param array $meta Extra metadata in `'key' => 'value'` format.
     */
    public function __construct(string $translation, array $meta = [])
    {
        $this->translation = $translation;
        $this->meta = $meta;
    }

    public function translation(): string
    {
        return $this->translation;
    }

    public function meta(): array
    {
        return $this->meta;
    }
}
