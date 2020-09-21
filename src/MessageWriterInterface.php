<?php

declare(strict_types=1);

namespace Yiisoft\Translator;

interface MessageWriterInterface
{
    public function write(array $messages): void;
}
