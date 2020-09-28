<?php

declare(strict_types=1);

namespace Yiisoft\Translator;

final class Category
{
    private MessageReaderInterface $reader;

    private MessageFormatterInterface $formatter;

    public function __construct(MessageReaderInterface $reader, MessageFormatterInterface $formatter)
    {
        $this->reader = $reader;
        $this->formatter = $formatter;
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
