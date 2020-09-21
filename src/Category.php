<?php

namespace Yiisoft\Translator;

final class Category
{
    /**
     * @var MessageReaderInterface
     */
    private $reader;

    /**
     * @var MessageFormatterInterface
     */
    private $formatter;

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
