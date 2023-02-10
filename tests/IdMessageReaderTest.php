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

        $this->assertSame('test', $reader->getMessage('test', 'my-module', 'en-US'));
    }

    public function testGetMessages(): void
    {
        $reader = new IdMessageReader();

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('IdMessageReader doesn\'t support getting all messages at once');
        $reader->getMessages('my-module', 'en-US');
    }
}
