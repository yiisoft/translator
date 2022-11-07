<?php

declare(strict_types=1);

namespace Yiisoft\Translator\Tests;

use PHPUnit\Framework\TestCase;
use Yiisoft\Di\Container;
use Yiisoft\Di\ContainerConfig;
use Yiisoft\Di\StateResetter;
use Yiisoft\Translator\MessageReaderInterface;
use Yiisoft\Translator\Translator;
use Yiisoft\Translator\TranslatorInterface;

final class ConfigTest extends TestCase
{
    public function testBase(): void
    {
        $container = $this->createContainer();

        $translator = $container->get(TranslatorInterface::class);

        $this->assertInstanceOf(Translator::class, $translator);
        $this->assertSame('test', $translator->translate('a'));
    }

    public function testReset(): void
    {
        $container = $this->createContainer();

        $translator = $container->get(TranslatorInterface::class);
        $translator->setLocale('ru_RU');

        $container->get(StateResetter::class)->reset();

        $this->assertSame('en_US', $translator->getLocale());
    }

    private function createContainer(?array $params = null): Container
    {
        return new Container(
            ContainerConfig::create()->withDefinitions(
                $this->getCommonDefinitions($params)
            )
        );
    }

    private function getCommonDefinitions(?array $params = null): array
    {
        if ($params === null) {
            $params = $this->getParams();
        }
        $common = require dirname(__DIR__) . '/config/common.php';

        return array_merge($this->getCategorySourceDefinition($params), $common);
    }

    private function getParams(): array
    {
        return require dirname(__DIR__) . '/config/params.php';
    }

    private function getCategorySourceDefinition(array $params): array
    {
        $messageReader = $this->createMock(MessageReaderInterface::class);
        $messageReader
            ->method('getMessage')
            ->willReturn('test');

        return [
            'translation.app' => [
                'definition' => static function () use ($messageReader, $params) {
                    $messageFormatter = new \Yiisoft\Translator\SimpleMessageFormatter();

                    return new \Yiisoft\Translator\CategorySource(
                        $params['yiisoft/translator']['defaultCategory'],
                        $messageReader,
                        $messageFormatter,
                    );
                },
                'tags' => ['translation.categorySource'],
            ],
        ];
    }
}
