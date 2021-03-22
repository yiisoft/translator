<?php

declare(strict_types=1);

namespace Yiisoft\Translator\Extractor;

/**
 * Extractor messages
 */
final class ContentParser
{
    private string $translator = '->translate';

    /** @var array<string|array{0: int, 1: string, 2: int}> */
    private array $translatorTokens = [];

    private int $sizeOfTranslator = 0;

    private string $defaultCategory = '';

    private static array $commaSpare = [
        ')' => '(',
        ']' => '[',
        '}' => '{',
    ];

    /**
     * @psalm-var array<array<string|array{0: int, 1: string, 2: int}>>
     */
    private array $skippedLines = [];

    public function __construct(?string $defaultCategory = null, ?string $translator = null)
    {
        $this->defaultCategory = $defaultCategory ?? $this->defaultCategory;
        $this->setTranslator($translator === null ? $this->translator : $translator);
    }

    private function setTranslator(string $translator): void
    {
        $this->translator = $translator;
        $translatorTokens = token_get_all('<?php ' . $this->translator);
        array_shift($translatorTokens);
        $this->translatorTokens = $translatorTokens;
        $this->sizeOfTranslator = count($this->translatorTokens);

        if ($this->sizeOfTranslator < 2) {
            throw new \RuntimeException('Translator tokens cannot be shorttest 2 tokens.');
        }
    }

    /**
     * @param string $content
     *
     * @psalm-return array<array-key|string, mixed|non-empty-list<string>>
     * @return array[]
     */
    public function extract(string $content): array
    {
        $this->skippedLines = [];
        $tokens = token_get_all($content);

        return $this->extractMessagesFromTokens($tokens);
    }

    /**
     * @psalm-param array<string|array{0: int, 1: string, 2: int}> $tokens
     *
     * @param array $tokens
     *
     * @psalm-return array<array-key|string, mixed|non-empty-list<string>>
     *
     * @return array
     */
    private function extractMessagesFromTokens(array $tokens): array
    {
        $messages = $buffer = [];
        $matchedTokensCount = $pendingParenthesisCount = 0;
        $isStartedTranslator = false;

        foreach ($tokens as $tokenIndex => $token) {
            if (in_array($token[0], [T_WHITESPACE, T_COMMENT])) {
                continue;
            }

            if ($isStartedTranslator) {
                if ($this->tokensEqual($token, ')')) {
                    if ($pendingParenthesisCount === 0) {
                        $messages = array_merge_recursive($messages, $this->extractParametersFromTokens($buffer));
                        $isStartedTranslator = false;
                        $pendingParenthesisCount = 0;
                        $buffer = [];
                        continue;
                    }
                    $pendingParenthesisCount--;
                } elseif ($this->tokensEqual($token, '(')) {
                    $pendingParenthesisCount++;
                }
                $buffer[] = $token;
            } else {
                if ($matchedTokensCount === $this->sizeOfTranslator) {
                    if ($this->tokensEqual($token, '(')) {
                        $isStartedTranslator = true;
                        continue;
                    }
                    $matchedTokensCount = 0;
                }

                if ($this->tokensEqual($token, $this->translatorTokens[$matchedTokensCount])) {
                    $matchedTokensCount++;
                } else {
                    $matchedTokensCount = 0;
                }
            }
        }

        return $messages;
    }

    /**
     * @param array $tokens
     * @psalm-param array<string|array{0: int, 1: string, 2: int}> $tokens
     *
     * @return array
     * @psalm-return array<array-key|string, mixed|non-empty-list<string>>
     */
    private function extractParametersFromTokens(array $tokens): array
    {
        $parameters = $this->splitTokensAsParams($tokens);

        if (!isset($parameters['id'])) {
            $this->skippedLines[] = $tokens;
            return [];
        }

        $messages = [$parameters['category'] ?? $this->defaultCategory => [$parameters['id']]];

        // Get translation messages from parameters
        if (isset($parameters['parameters'])) {
            $messages = array_merge_recursive($messages, $this->extractMessagesFromTokens($parameters['parameters']));
        }

        return $messages;
    }

    /**
     * @psalm-param array<string|array{0: int, 1: string, 2: int}> $tokens
     *
     * @psalm-return array{category?: null|string, id?: null|string, parameters?: null|list<array{0: int, 1: string, 2: int}|string>}
     */
    private function splitTokensAsParams(array $tokens): array
    {
        $parameters = [];
        $parameterIndex = 0;
        $commaStack = [];

        foreach ($tokens as $token) {
            if (empty($commaStack) && $token === ',') {
                $parameterIndex++;
                continue;
            }
            if (is_string($token)) {
                if (in_array($token, self::$commaSpare)) {
                    $commaStack[] = $token;
                } elseif (isset(self::$commaSpare[$token]) && array_pop($commaStack) !== self::$commaSpare[$token]) {
                    return [];
                }
            }
            $parameters[$parameterIndex][] = $token;
        }

        return [
            'id' => $this->getMessageStringFromTokens($parameters[0] ?? []),
            'parameters' => $parameters[1] ?? null,
            'category' => $this->getMessageStringFromTokens($parameters[2] ?? []),
        ];
    }

    /**
     * @psalm-param array<string|array{0: int, 1: string, 2: int}> $tokens
     *
     * @return string|null
     */
    private function getMessageStringFromTokens(array $tokens): ?string
    {
        if (empty($tokens) || $tokens[0][0] !== T_CONSTANT_ENCAPSED_STRING) {
            return null;
        }

        $fullMessage = substr($tokens[0][1], 1, -1);

        $i = 1;
        $countTokens = count($tokens);
        while ($i < $countTokens && $tokens[$i] === '.') {
            if ($tokens[$i + 1][0] === T_CONSTANT_ENCAPSED_STRING) {
                $fullMessage .= substr($tokens[$i + 1][1], 1, -1);
            } elseif (in_array($tokens[$i + 1][0], [T_LNUMBER, T_DNUMBER])) {
                $fullMessage .= $tokens[$i + 1][1];
            } else {
                return null;
            }

            $i += 2;
        }

        return stripcslashes($fullMessage);
    }

    /**
     * Finds out if two PHP tokens are equal.
     *
     * @param array{0: int, 1: string, 2: int}|string $a
     * @param array{0: int, 1: string, 2: int}|string $b
     *
     * @return bool
     */
    private function tokensEqual($a, $b): bool
    {
        if (is_string($a)) {
            return $a === $b;
        }

        return $a[0] === $b[0] && $a[1] == $b[1];
    }

    public function getDefaultCategory(): string
    {
        return $this->defaultCategory;
    }

    public function setDefaultCategory(string $defaultCategory): void
    {
        $this->defaultCategory = $defaultCategory;
    }

    public function hasSkippedLines(): bool
    {
        return !empty($this->skippedLines);
    }

    /**
     * @psalm-return array<array<string|array{0: int, 1: string, 2: int}>>
     */
    public function getSkippedLines(): array
    {
        return $this->skippedLines;
    }
}
