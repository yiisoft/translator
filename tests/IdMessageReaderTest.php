<?php

declare(strict_types=1);

namespace Yiisoft\Translator\Tests;

use PHPUnit\Framework\TestCase;
use RuntimeException;
use Yiisoft\Translator\IdMessageReader;

final class IdMessageReaderTest extends TestCase
{
    public function testGetMessage(): void
    {
        $reader = new IdMessageReader();

        $this->assertSame('test', $reader->getMessage('test', 'my-module', 'en_US'));
    }

    public function testGetMessages(): void
    {
        $reader = new IdMessageReader();

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('IdMessageReader do not support receiving all messages.');
        $reader->getMessages('my-module', 'en_US');
    }
}
