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
    private string $translator='->translate';
    private array $skippedLines = [];
    private array $skippedLinesOfFile = [];

    private string $defaultCategory = '';

    private array $only = ['**.php'];
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

    public function extract(string $path, array $options = []): array
    {
        if (!is_dir($path)) {
            throw new \RuntimeException(sprintf('Directory "%s" does not exist.', $path));
        }

        $messages = $this->getMessageFromPath($path, $options);

        return $messages;
    }

    private function getMessageFromPath(string $path, array $options = []): array
    {
        $messages = [];

        $files = FileHelper::findFiles($path, [
            'filter' => (new PathMatcher())
                ->only(... isset($options['only']) ? (array)$options['only'] : $this->only)
                ->except(... isset($options['except']) ? (array)$options['except'] : $this->except),
            'recursive' => true,
        ]);

        foreach ($files as $file) {
            $messages = array_merge_recursive($messages, $this->extractMessagesFromFile($file));
        }

        return $messages;
    }

    private function extractMessagesFromFile(string $fileName): array
    {
        $fileContent = file_get_contents($fileName);
        $tokens = token_get_all($fileContent);

        $translatorTokens = token_get_all('<?php ' . $this->translator);
        array_shift($translatorTokens);

        $this->skippedLinesOfFile = [];
        $messages = $this->extractMessagesFromTokens($tokens, $translatorTokens);

        if (!empty($this->skippedLinesOfFile)) {
            $this->skippedLines[$fileName] = $this->skippedLinesOfFile;
        }

        return $messages;
    }

    private function extractMessagesFromTokens(array $tokens, array $translatorTokens): array
    {
        $messages = $buffer = [];
        $matchedTokensCount = $pendingParenthesisCount = 0;
        $isStartedTranslator = false;

        $sizeofTranslator = count($translatorTokens);

        foreach ($tokens as $tokenIndex => $token) {
            if (is_array($token) && in_array($token[0], [T_WHITESPACE, T_COMMENT])) {
                continue;
            }

            if ($isStartedTranslator) {
                if ($this->tokensEqual($token, ')') && $pendingParenthesisCount === 0) {
                    $messages = array_merge_recursive($messages, $this->extractParametersFromTokens($buffer, $translatorTokens));
                    $isStartedTranslator = false;
                } else {
                    if ($this->tokensEqual($token, '(')) {
                        $pendingParenthesisCount++;
                    } elseif($this->tokensEqual($token, ')')) {
                        $pendingParenthesisCount--;
                    }
                    $buffer[] = $token;
                }

            } else {
                if ($sizeofTranslator === $matchedTokensCount) {
                    if ($this->tokensEqual($token, '(')) {
                        $isStartedTranslator = true;
                        $pendingParenthesisCount = 0;
                        $buffer = [];
                        continue;
                    }

                    $matchedTokensCount = 0;
                }

                if ($this->tokensEqual($token, $translatorTokens[$matchedTokensCount])) {
                    $matchedTokensCount++;
                } else {
                    $matchedTokensCount = 0;
                }
            }
        }

        return $messages;
    }

    private function extractParametersFromTokens(array $tokens, array $translatorTokens): array
    {
        $messages = [];
        $parameters = $this->splitTokensAsParams($tokens);

        if ($parameters['id'] === null) {
            $this->skippedLinesOfFile[] = $tokens;
        } else {
            $messages[$parameters['category'] ?? $this->defaultCategory][] = $parameters['id'];

            // Get translation messages from parameters
            if ($parameters['parameters'] !== null) {
                $messages = array_merge_recursive($messages, $this->extractMessagesFromTokens($parameters['parameters'], $translatorTokens));
            }
        }

        return $messages;
    }

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
                if (in_array($token, static::$commaSpare)) {
                    array_push($commaStack, $token);
                } elseif(isset(static::$commaSpare[$token])) {
                    if (array_pop($commaStack) !== static::$commaSpare[$token]) {
                        return [];
                    }
                }
            }
            $parameters[$parameterIndex][] = $token;
        }

        return [
            'id' => isset($parameters[0]) ? $this->getMessageStringFromTokens($parameters[0]) : null,
            'parameters' => isset($parameters[1]) ? $parameters[1] : null,
            'category' => isset($parameters[2]) ? $this->getMessageStringFromTokens($parameters[2]) : null,
        ];
    }

    private function getMessageStringFromTokens(array $tokens): ?string
    {
        if ($tokens[0][0] !== T_CONSTANT_ENCAPSED_STRING) {
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
     * @param array|string $a
     * @param array|string $b
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
}
