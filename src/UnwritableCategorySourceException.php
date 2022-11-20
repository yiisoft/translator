<?php

declare(strict_types=1);

namespace Yiisoft\Translator;

use Exception;
use Throwable;

final class UnwritableCategorySourceException extends Exception
{
    public function __construct(string $categoryName, ?Throwable $previous = null)
    {
        parent::__construct(sprintf('Unable to write into category with name "%s".', $categoryName), 0, $previous);
    }
}
