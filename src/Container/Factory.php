<?php

declare(strict_types=1);

namespace Webinertia\Validator\Container;

use Laminas\Db\Adapter\AdapterInterface;
use Laminas\Db\Adapter\AdapterAwareInterface;
use Laminas\Stdlib\ArrayUtils;
use Laminas\Validator\ValidatorInterface;
use Psr\Container\ContainerInterface;

/** @internal */
final class Factory
{
    public const APP_SETTINGS_KEY = 'app_settings';

    public function __invoke(ContainerInterface $container, string $requestedName, array $options = []): ValidatorInterface
    {
        if (! $container->has('config')) {
            return new $requestedName();
        }
        $validatorConfig = [];
        $config          = $container->get('config');
        // all others would probably just add config top level
        if (! empty($config[$requestedName])) {
            $validatorConfig = $config[$requestedName];
        }
        // prefer Webinertia config
        if (! empty($config[static::APP_SETTINGS_KEY][$requestedName])) {
            $validatorConfig = $config[static::APP_SETTINGS_KEY][$requestedName];
        }
        $merged = ArrayUtils::merge($validatorConfig, $options);
        $instance = new $requestedName($merged);

        if ($instance instanceof AdapterAwareInterface && $container->has(AdapterInterface::class)) {
            $instance->setDbAdapter($container->get(AdapterInterface::class));
        }
        return $instance;
    }
}
