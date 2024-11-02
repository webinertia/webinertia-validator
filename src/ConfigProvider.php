<?php

declare(strict_types=1);

namespace Webinertia\Validator;

final class ConfigProvider
{
    public function __invoke(): array
    {
        return [
            'dependencies' => $this->getDependencyConfig(),
        ];
    }

    public function getDependencyConfig(): array
    {
        return [
            'factories' => [
                PasswordRequirement::class => Container\Factory::class,
            ],
        ];
    }
}
