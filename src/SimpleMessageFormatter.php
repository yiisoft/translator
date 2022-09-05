<?php

declare(strict_types=1);

namespace Yiisoft\Translator;

class SimpleMessageFormatter implements MessageFormatterInterface
{
    public function format(string $message, array $parameters, string $locale): string
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
                    preg_match_all('/([^{}\s]+)({(.*?)})/', $options, $pluralMatches);
                    $map = array_combine($pluralMatches[1], $pluralMatches[3]);
                    $formattedValue = $value . ' ';
                    $formattedValue .= $value === 1 ? $map['one'] : $map['other'];
                    $replacements[$match] = $formattedValue;

                    break;
                default:
                    $replacements[$match] = $value;
            }
        }

        return strtr($message, $replacements);
    }
}
