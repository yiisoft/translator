<?php

declare(strict_types=1);

namespace Yiisoft\Translator;

use InvalidArgumentException;

class SimpleMessageFormatter implements MessageFormatterInterface
{
    private const PLURAL_ONE = 'one';
    private const PLURAL_OTHER = 'other';
    private const PLURAL_KEYS = [self::PLURAL_ONE, self::PLURAL_OTHER];

    public function format(string $message, array $parameters, string $locale = 'en_US'): string
    {
        preg_match_all('/{((?>[^{}]+)|(?R))*}/', $message, $matches);
        $replacements = [];

        foreach ($matches[0] as $match) {
            $parts = explode(',', $match);
            $parameter = trim($parts[0], '{}');
            $value = $parameters[$parameter];

            if (!is_scalar($value)) {
                continue;
            }

            if (count($parts) === 1) {
                $replacements[$match] = $value;

                continue;
            }

            $format = ltrim($parts[1]);
            $format = rtrim($format, '}');

            switch ($format) {
                case 'plural':
                    $options = $parts[2];
                    $replacements[$match] = self::pluralize($value, $options);

                    break;
                default:
                    $replacements[$match] = $value;
            }
        }

        return strtr($message, $replacements);
    }

    private static function pluralize(int $value, string $options): string
    {
        preg_match_all('/([^{}\s]+)({(.*?)})/', $options, $pluralMatches);

        foreach ($pluralMatches[1] as $match) {
            if (!in_array($match, self::PLURAL_KEYS, true)) {
                $keysStr = implode(', ', array_map(fn(string $value): string => '"' . $value . '"', self::PLURAL_KEYS));

                throw new InvalidArgumentException("Invalid plural key - \"$match\". The valid keys are $keysStr.");
            }
        }

        $map = array_combine($pluralMatches[1], $pluralMatches[3]);
        $formattedValue = $value . ' ';
        $formattedValue .= $value === 1 ? $map[self::PLURAL_ONE] : $map[self::PLURAL_OTHER];

        return $formattedValue;
    }
}
