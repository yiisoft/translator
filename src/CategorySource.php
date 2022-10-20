<?php

declare(strict_types=1);

namespace Yiisoft\Translator;

use RuntimeException;

/**
 * Represents message category.
 */
final class CategorySource
{
    private string $name;

    /**
     * @param string $name Category name.
     * @param MessageReaderInterface $reader Message reader to get messages from for this category.
     * @param MessageFormatterInterface|null $formatter Message formatter to format messages with for this category.
     */
    public function __construct(
        string $name,
        private MessageReaderInterface $reader,
        private ?MessageFormatterInterface $formatter = null
    ) {
        if (!preg_match('/^[a-z0-9_-]+$/i', $name)) {
            throw new RuntimeException('Category name is invalid. Only letters and numbers are allowed.');
        }
        $this->name = $name;
    }

    /**
     * @return string Category name.
     */
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
     * @param string $locale Locale to use. Usually affects formatting numbers, dates etc.
     * @param MessageFormatterInterface $defaultFormatter Message formatter that will be used if formatter not specified
     * in message category.
     *
     * @return string Formatted message.
     */
    public function format(
        string $message,
        array $parameters,
        string $locale,
        MessageFormatterInterface $defaultFormatter
    ): string {
        return ($this->formatter ?? $defaultFormatter)->format($message, $parameters, $locale);
    }
}
