<?php

declare(strict_types=1);

namespace Webinertia\Validator;

final class Module
{
    public function getConfig(): array
    {
        $configProvider = new ConfigProvider();
        return [
            'validators' => $configProvider->getDependencyConfig(),
        ];
    }
}
