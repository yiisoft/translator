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
        $this->name = $name;
        $this->reader = $reader;
        $this->formatter = $formatter;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getReader(): MessageReaderInterface
    {
        return $this->reader;
    }

    public function getFormatter(): MessageFormatterInterface
    {
        return $this->formatter;
    }
}
