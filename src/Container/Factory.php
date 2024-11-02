<?php

declare(strict_types=1);

namespace Webinertia\Validator\Container;

use Laminas\Validator\ValidatorInterface;
use Psr\Container\ContainerInterface;

final class Factory
{
    public final const APP_SETTINGS_KEY = 'app_settings';

    public function __invoke(ContainerInterface $container, string $requestedName): ValidatorInterface
    {
        if (! $container->has('config')) {
            return new $requestedName();
        }
        $config = $container->get('config');
        // prefer Webinertia config
        if (! empty($config[static::APP_SETTINGS_KEY][$requestedName::class])) {
            return new $requestedName($config[static::APP_SETTINGS_KEY][$requestedName::class]);
        }
        // all others would probably just add config top level
        if (! empty($config[$requestedName::class])) {
            return new $requestedName($config[$requestedName::class]);
        }
    }
}
