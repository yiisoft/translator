<?php

declare(strict_types=1);

namespace Yiisoft\Translator;

class SimpleMessageFormatter implements MessageFormatterInterface
{
    public function format(string $message, array $parameters, string $locale): string
    {
        $replacements = [];
        /** @var mixed $value */
        foreach ($parameters as $key => $value) {
            if (is_scalar($value)) {
                $replacements['{' . $key . '}'] = $value;
            }
        }
        return strtr($message, $replacements);
    }
}
