<?php

declare(strict_types=1);

namespace Yiisoft\Translator;

use InvalidArgumentException;

use function count;
use function in_array;
use function is_int;

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

            if (!isset($parameters[$parameter])) {
                throw new InvalidArgumentException("\"$parameter\" parameter's value is missing.");
            }

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
                    $options = $parts[2] ?? '';
                    $replacements[$match] = self::pluralize($value, $options);

                    break;
                default:
                    $replacements[$match] = $value;
            }
        }

        return strtr($message, $replacements);
    }

    private static function pluralize(mixed $value, string $options): string
    {
        if (!$options) {
            throw new InvalidArgumentException('Missing plural keys: ' . self::formatList(self::PLURAL_KEYS) . '.');
        }

        if (!is_int($value)) {
            throw new InvalidArgumentException('Only integer numbers are supported with plural format.');
        }

        preg_match_all('/([^{}\s]+)({(.*?)})/', $options, $pluralMatches);

        $map = [];
        foreach ($pluralMatches[1] as $index => $match) {
            if (!in_array($match, self::PLURAL_KEYS, true)) {
                $keysStr = self::formatList(self::PLURAL_KEYS);

                throw new InvalidArgumentException("Invalid plural key - \"$match\". The valid keys are $keysStr.");
            }

            $map[$match] = $pluralMatches[3][$index];
        }

        $diff = array_diff(self::PLURAL_KEYS, $pluralMatches[1]);
        if ($diff !== []) {
            throw new InvalidArgumentException('Missing plural keys: ' . self::formatList($diff) . '.');
        }

        return $value === 1 ? $map[self::PLURAL_ONE] : $map[self::PLURAL_OTHER];
    }

    /**
     * @param string[] $items
     */
    private static function formatList(array $items): string
    {
        return implode(', ', array_map(fn (string $value): string => '"' . $value . '"', $items));
    }
}
