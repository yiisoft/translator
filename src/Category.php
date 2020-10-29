<?php

declare(strict_types=1);

namespace Yiisoft\Translator;

final class Category
{
    private string $name;
    private MessageReaderInterface $reader;
    private MessageFormatterInterface $formatter;

    public function __construct(string $name, MessageReaderInterface $reader, MessageFormatterInterface $formatter)
    {
        if (!preg_match('/^[a-z0-9_-]+$/si', $name)) {
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

    public function getMessage(string $id, string $locale, array $parameters = []): ?string
    {
        return $this->reader->getMessage($id, $this->name, $locale, $parameters);
    }

    public function format(string $message, array $parameters, string $locale): string
    {
        return $this->formatter->format($message, $parameters, $locale);
    }
}
