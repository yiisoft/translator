<?php

declare(strict_types=1);

namespace Yiisoft\Translator\Extractor;

use Yiisoft\Files\FileHelper;
use Yiisoft\Files\PathMatcher\PathMatcher;

/**
 * Extractor messages
 */
final class TranslationExtractor
{
    private string $path;

    /** @var string[] */
    private array $only = ['**.php'];

    /** @psalm-var array<string, array<array<string|array{0: int, 1: string, 2: int}>>> */
    private array $skippedLines = [];

    /** @psalm-suppress PropertyNotSetInConstructor */
    private ?ContentParser $parser;

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

    private static array $commaSpare = [
        ')' => '(',
        ']' => '[',
        '}' => '{',
    ];

    /**
     * TranslationExtractor constructor.
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

        if (isset($only)) {
            $this->only = $only;
        }

        if (isset($except)) {
            $this->except = $except;
        }
    }

    /**
     * @param string|null $defaultCategory
     * @param string|null $translator
     *
     * @return array
     */
    public function extract(?string $defaultCategory = null, ?string $translator = null): array
    {
        $messages = [];
        $this->parser = new ContentParser($defaultCategory, $translator);

        $files = FileHelper::findFiles($this->path, [
            'filter' => (new pathMatcher())->only(...$this->only)->except(...$this->except),
            'recursive' => true,
        ]);

        /** @var string[] $files */
        foreach ($files as $file) {
            $fileContent = file_get_contents($file);
            $messages = array_merge_recursive($messages, $this->parser->extract($fileContent));
            if ($this->parser->hasSkippedLines()) {
                $this->skippedLines[$file] = $this->parser->getSkippedLines();
            }
        }

        return $messages;
    }

    public function hasSkippedLines(): bool
    {
        return !empty($this->skippedLines);
    }

    /**
     * @psalm-return array<string, array<array<string|array{0: int, 1: string, 2: int}>>>
     */
    public function getSkippedLines(): array
    {
        return $this->skippedLines;
    }
}
