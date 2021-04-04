<?php

declare(strict_types=1);

namespace Yiisoft\Translator\Extractor;

use Yiisoft\Files\FileHelper;
use Yiisoft\Files\PathMatcher\PathMatcher;

/**
 * Extracts translator IDs from files within a given path.
 */
final class TranslationExtractor
{
    private string $path;

    /** @var string[] */
    private array $only = ['**.php'];

    private array $skippedLines = [];

    /** @var string[] */
    private array $except = [
        '.svn',
        '.git',
        '.gitignore',
        '.gitkeep',
        '.hgignore',
        '.hgkeep',
        '/messages',
    ];

    /**
     * TranslationExtractor constructor.
     *
     * @param string $path
     * @param string[]|null $only
     * @param string[]|null $except
     */
    public function __construct(string $path, ?array $only = null, ?array $except = null)
    {
        if (!is_dir($path)) {
            throw new \RuntimeException(sprintf('Directory "%s" does not exist.', $path));
        }

        $this->path = $path;

        if ($only !== null) {
            $this->only = $only;
        }

        if ($except !== null) {
            $this->except = $except;
        }
    }

    public function extract(string $defaultCategory = 'app', ?string $translatorCall = null): array
    {
        $messages = [];
        $parser = new ContentParser($defaultCategory, $translatorCall);

        $files = FileHelper::findFiles($this->path, [
            'filter' => (new pathMatcher())->only(...$this->only)->except(...$this->except),
            'recursive' => true,
        ]);

        /** @var string[] $files */
        foreach ($files as $file) {
            $fileContent = file_get_contents($file);
            $messages = array_merge_recursive($messages, $parser->extract($fileContent));
            if ($parser->hasSkippedLines()) {
                $this->skippedLines[$file] = $parser->getSkippedLines();
            }
        }

        return $messages;
    }

    public function hasSkippedLines(): bool
    {
        return !empty($this->skippedLines);
    }

    /**
     * ```php
     * return [
     *     'fileName' => [
     *         [
     *             int $numberOfLine,
     *             string $incorrectLine,
     *         ],
     *     ],
     * ]
     * ```
     *
     * @return array
     */
    public function getSkippedLines(): array
    {
        return $this->skippedLines;
    }
}
