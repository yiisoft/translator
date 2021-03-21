<?php

declare(strict_types=1);

namespace Yiisoft\Translator;

use Yiisoft\Files\FileHelper;
use Yiisoft\Files\PathMatcher\PathMatcher;

/**
 * Extractor messages
 */
final class TranslationExtractor
{
    private string $translator = '->translate';

    /**
     * @var array<string|array{0: int, 1: string, 2: int}>
     */
    private array $translatorTokens = [];
    private int $sizeOfTranslator = 0;

    private array $skippedLines = [];
    private array $skippedLinesOfFile = [];

    private string $defaultCategory = '';

    /**
     * @var string[]
     */
    private array $only = ['**.php'];

    /**
     * @var string[]
     */
    private array $except = [
        '.svn',
        '.git',
        '.gitignore',
        '.gitkeep',
        '.hgignore',
        '.hgkeep',
        '/messages',
    ];

    private static array $commaSpare = [
        ')' => '(',
        ']' => '[',
        '}' => '{',
    ];

    /**
     * @param string $path
     * @param string[]|null $only
     * @param string[]|null $except
     *
     * @return array
     */
    public function extract(string $path, ?array $only = null, ?array $except = null): array
    {
        if (!is_dir($path)) {
            throw new \RuntimeException(sprintf('Directory "%s" does not exist.', $path));
        }

        $translatorTokens = token_get_all('<?php ' . $this->translator);
        array_shift($translatorTokens);
        $this->translatorTokens = $translatorTokens;
        $this->sizeOfTranslator = count($this->translatorTokens);


        if ($this->sizeOfTranslator < 2) {
            throw new \RuntimeException('Translator tokens cannot be shorttest 2 tokens.');
        }

        return $this->getMessageFromPath($path, $only === null ? $this->only : $only, $except === null ? $this->except : $except);
    }

    /**
     * @param string $path
     * @param string[] $only
     * @param string[] $except
     *
     * @psalm-return array<array-key|string, mixed|non-empty-list<string>>
     */
    private function getMessageFromPath(string $path, array $only, array $except): array
    {
        $messages = [];

        $files = FileHelper::findFiles($path, [
            'filter' => (new pathMatcher())->only(...$only)->except(...$except),
            'recursive' => true,
        ]);

        /**
         * @var string[] $files
         */
        foreach ($files as $file) {
            $messages = array_merge_recursive($messages, $this->extractMessagesFromFile($file));
        }

        return $messages;
    }

    /**
     * @param string $fileName
     *
     * @psalm-return array<array-key|string, mixed|non-empty-list<string>>
     */
    private function extractMessagesFromFile(string $fileName): array
    {
        $fileContent = file_get_contents($fileName);
        $tokens = token_get_all($fileContent);

        $this->skippedLinesOfFile = [];
        $messages = $this->extractMessagesFromTokens($tokens);

        if (!empty($this->skippedLinesOfFile)) {
            $this->skippedLines[$fileName] = $this->skippedLinesOfFile;
        }

        return $messages;
    }

    /**
     * @psalm-param array<string|array{0: int, 1: string, 2: int}> $tokens
     *
     * @psalm-return array<array-key|string, mixed|non-empty-list<string>>
     */
    private function extractMessagesFromTokens(array $tokens): array
    {
        $messages = $buffer = [];
        $matchedTokensCount = $pendingParenthesisCount = 0;
        $isStartedTranslator = false;

        foreach ($tokens as $tokenIndex => $token) {
            if (is_array($token) && in_array($token[0], [T_WHITESPACE, T_COMMENT])) {
                continue;
            }

            if ($isStartedTranslator) {
                if ($this->tokensEqual($token, ')') && $pendingParenthesisCount === 0) {
                    $messages = array_merge_recursive($messages, $this->extractParametersFromTokens($buffer));
                    $isStartedTranslator = false;
                    $pendingParenthesisCount = 0;
                    $buffer = [];
                } else {
                    if ($this->tokensEqual($token, '(')) {
                        $pendingParenthesisCount++;
                    } elseif ($this->tokensEqual($token, ')')) {
                        $pendingParenthesisCount--;
                    }
                    $buffer[] = $token;
                }
            } else {
                if ($matchedTokensCount === $this->sizeOfTranslator) {
                    if ($this->tokensEqual($token, '(')) {
                        $isStartedTranslator = true;
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

        if ($parameters === null || $parameters['id'] === null) {
            $this->skippedLinesOfFile[] = $tokens;
            return [];
        }

        $messages = [$parameters['category'] ?? $this->defaultCategory => [$parameters['id']]];

        // Get translation messages from parameters
        if ($parameters['parameters'] !== null) {
            $messages = array_merge_recursive($messages, $this->extractMessagesFromTokens($parameters['parameters']));
        }

        return $messages;
    }

    /**
     * @psalm-param array<string|array{0: int, 1: string, 2: int}> $tokens
     *
     * @psalm-return null|array{category: null|string, id: null|string, parameters: non-empty-list<array{0: int, 1: string, 2: int}|string>|null}
     */
    private function splitTokensAsParams(array $tokens): ?array
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
                } elseif (isset(self::$commaSpare[$token])&& array_pop($commaStack) !== self::$commaSpare[$token]) {
                    return null;
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
        while ($i < count($tokens) && $tokens[$i] === '.') {
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

    public function hasSkippedLines(): bool
    {
        return !empty($this->skippedLines);
    }

    public function getSkippedLines(): array
    {
        return $this->skippedLines;
    }

    public function getDefaultCategory(): string
    {
        return $this->defaultCategory;
    }

    public function setDefaultCategory(string $defaultCategory): void
    {
        $this->defaultCategory = $defaultCategory;
    }

    public function withTranslator(string $translator): self
    {
        $new = clone $this;
        $new->translator = $translator;
        return $new;
    }
}
