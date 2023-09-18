<?php

declare(strict_types=1);

namespace Webinertia\Validator;

use Laminas\ServiceManager\Factory;

final class ConfigProvider
{
    public function __invoke():  array
    {
        return [
            'dependencies' => $this->getDependencyConfig(),
        ];
    }
    public function getDependencyConfig(): array
    {
        return [
            'factories' => [
                Password::class => Factory\InvokableFactory::class,
            ],
        ];
    }
}
