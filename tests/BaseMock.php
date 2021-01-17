<?php

declare(strict_types=1);

namespace Yiisoft\Translator\Tests;

use Yiisoft\Translator\Category;
use Yiisoft\Translator\MessageFormatterInterface;
use Yiisoft\Translator\MessageReaderInterface;

abstract class BaseMock extends \PHPUnit\Framework\TestCase
{
    protected function createCategory(string $categoryName, array $messages = []): Category
    {
        return new Category(
            $categoryName,
            $this->createMessageReader($categoryName, $messages),
            $this->createMessageFormatter()
        );
    }

    protected function createMessageReader(string $category, array $messages): MessageReaderInterface
    {
        return new class($category, $messages) implements MessageReaderInterface {
            private string $category;
            private array $messages;

            public function __construct(string $category, array $messages)
            {
                $this->category = $category;
                $this->messages = $messages;
            }

            public function getMessage(string $id, string $category, string $locale, array $parameters = []): ?string
            {
                return $this->messages[$this->category][$locale][$id] ?? null;
            }
        };
    }

    protected function createMessageFormatter(): MessageFormatterInterface
    {
        return new class() implements MessageFormatterInterface {
            public function format(string $message, array $parameters, string $locale): string
            {
                $replacements = [];
                foreach ($parameters as $key => $value) {
                    if (is_array($value)) {
                        $value = 'array';
                    } elseif (is_object($value)) {
                        $value = 'object';
                    } elseif (is_resource($value)) {
                        $value = 'resource';
                    }
                    $replacements['{' . $key . '}'] = $value;
                }
                return strtr($message, $replacements);
//                return $message;
            }
        };
    }
}
