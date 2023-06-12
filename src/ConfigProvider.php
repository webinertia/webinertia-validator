<?php

declare(strict_types=1);

namespace Webinertia\Validator;

use Laminas\ServiceManager\Factory;

final class ConfigProvider
{
    public function getDependencyConfig(): array
    {
        return [];
    }

    public function getValidatorConfig(): array
    {
        return [
            'factories' => [
                Password::class => Factory\InvokableFactory::class,
            ],
        ];
    }
}
