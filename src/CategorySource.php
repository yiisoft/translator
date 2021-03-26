<?php

declare(strict_types=1);

namespace Yiisoft\Translator;

/**
 * Represents message category.
 */
final class CategorySource
{
    private string $name;
    private MessageReaderInterface $reader;
    private MessageFormatterInterface $formatter;

    public function __construct(string $name, MessageReaderInterface $reader, MessageFormatterInterface $formatter)
    {
        if (!preg_match('/^[a-z0-9_-]+$/i', $name)) {
            throw new \RuntimeException('Category name is invalid. Only letters and numbers are allowed.');
        }
        $this->name = $name;
        $this->reader = $reader;
        $this->formatter = $formatter;
    }

    public function getName(): string
    {
        return $this->name;
    }

    /**
     * Get a message with ID, locale and parameters specified.
     *
     * @param string $id Message ID.
     * @param string $locale Locale to get message for.
     * @param array $parameters Message parameters.
     *
     * @return string|null Message string or null if message was not found.
     */
    public function getMessage(string $id, string $locale, array $parameters = []): ?string
    {
        return $this->reader->getMessage($id, $this->name, $locale, $parameters);
    }

    /**
     * Format the message given parameters and locale.
     *
     * @param string $message Message to be formatted.
     * @param array $parameters Parameters to use.
     * @psalm-param array<array-key, mixed> $parameters
     *
     * @param string $locale Locale to use. Usually affects formatting numbers, dates etc.
     *
     * @return string Formatted message.
     */
    public function format(string $message, array $parameters, string $locale): string
    {
        return $this->formatter->format($message, $parameters, $locale);
    }
}
