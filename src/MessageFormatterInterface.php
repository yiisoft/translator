<?php

declare(strict_types=1);

namespace Yiisoft\Translator;

interface MessageFormatterInterface
{
    /**
     * @param string $message
     * @param array $parameters
     * @param string $locale
     * @return string
     */
    public function format(string $message, array $parameters, string $locale): string;
}
