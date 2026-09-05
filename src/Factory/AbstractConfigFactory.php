<?php

/**
 * This file is part of the Phalcon Framework.
 *
 * (c) Phalcon Team <team@phalcon.io>
 *
 * For the full copyright and license information, please view the LICENSE.txt
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Phalcon\Factory;

use Exception as BaseException;
use Phalcon\Config\ConfigInterface;
use Phalcon\Contracts\Factory\FactoryTypes;

use function is_array;

/**
 * @phpstan-import-type factory_config from FactoryTypes
 */
abstract class AbstractConfigFactory
{
    /**
     * Checks the config if it is a valid object
     *
     * @param array<string, mixed>|ConfigInterface $config
     *
     * @phpstan-param factory_config|ConfigInterface $config
     *
     * @phpstan-return factory_config
     * @throws BaseException
     */
    protected function checkConfig(mixed $config): array
    {
        if ($config instanceof ConfigInterface) {
            /** @phpstan-var factory_config $config */
            $config = $config->toArray();
        }

        if (!is_array($config)) {
            throw $this->getException(
                "Config must be array or Phalcon\\Config\\Config object"
            );
        }

        return $config;
    }

    /**
     * Checks if the config has "adapter"
     *
     * @param array<string, mixed> $config
     *
     * @phpstan-param factory_config $config
     *
     * @return array<string, mixed>
     * @phpstan-return factory_config
     * @throws BaseException
     */
    protected function checkConfigElement(array $config, string $element): array
    {
        if (!isset($config[$element])) {
            throw $this->getException(
                "You must provide the '" . $element
                . "' option in the factory config parameter."
            );
        }

        return $config;
    }

    /**
     * Returns the exception object for the child class
     */
    protected function getException(string $message): BaseException
    {
        $exception = $this->getExceptionClass();

        return new $exception($message);
    }

    /**
     * @phpstan-return class-string<BaseException>
     */
    protected function getExceptionClass(): string
    {
        return BaseException::class;
    }
}
