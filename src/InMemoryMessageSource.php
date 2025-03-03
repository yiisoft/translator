<?php

declare(strict_types=1);

namespace Yiisoft\Translator;

use function array_map;

/**
 * `InMemoryMessageSource` is a simple in-memory message source that can be used for testing purposes.
 */
final class InMemoryMessageSource implements MessageReaderInterface, MessageWriterInterface
{
    /**
     * @psalm-var array<string, array<string, array<string, string>>>
     */
    private array $messages = [];

    public function getMessage(string $id, string $category, string $locale, array $parameters = []): ?string
    {
        return $this->messages[$category][$locale][$id] ?? null;
    }

    public function getMessages(string $category, string $locale): array
    {
        return array_map(
            static fn($message) => ['message' => $message],
            $this->messages[$category][$locale] ?? []
        );
    }

    public function write(string $category, string $locale, array $messages): void
    {
        $this->messages[$category][$locale] = $messages;
    }
}
