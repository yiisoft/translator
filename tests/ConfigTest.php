<?php

declare(strict_types=1);

namespace Yiisoft\Translator\Tests;

use PHPUnit\Framework\TestCase;
use Yiisoft\Di\Container;
use Yiisoft\Di\ContainerConfig;
use Yiisoft\Di\StateResetter;
use Yiisoft\Translator\Translator;
use Yiisoft\Translator\TranslatorInterface;

final class ConfigTest extends TestCase
{
    public function testBase(): void
    {
        $container = $this->createContainer();

        $translator = $container->get(TranslatorInterface::class);

        $this->assertInstanceOf(Translator::class, $translator);
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
        return require dirname(__DIR__) . '/config/common.php';
    }

    private function getParams(): array
    {
        return require dirname(__DIR__) . '/config/params.php';
    }
}
