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

namespace Phalcon\Support\Traits;

use Phalcon\Config\ConfigInterface;
use Throwable;

use function is_array;

trait ConfigTrait
{
    /**
     * Normalizes the factory configuration. The parameter is `mixed` on
     * purpose: anything that is neither an array nor a `ConfigInterface` is
     * rejected here at runtime.
     *
     * @return array<string, mixed>
     */
    protected function checkConfig(mixed $config): array
    {
        if ($config instanceof ConfigInterface) {
            /** @var array<string, mixed> $converted */
            $converted = $config->toArray();

            return $converted;
        }

        if (!is_array($config)) {
            $exception = $this->getExceptionClass();
            throw new $exception(
                "Config must be array or Phalcon\\Config\\Config object"
            );
        }

        /** @var array<string, mixed> $config */
        return $config;
    }

    /**
     * Checks if the config has a specific element
     *
     * @param array<string, mixed> $config
     *
     * @return array<string, mixed>
     */
    protected function checkConfigElement(array $config, string $element): array
    {
        if (!isset($config[$element])) {
            $exception = $this->getExceptionClass();
            throw new $exception(
                "You must provide the '" . $element . "' option in the factory config parameter."
            );
        }

        return $config;
    }

    /**
     * Returns the exception class for the factory
     *
     * @return class-string<Throwable>
     */
    abstract protected function getExceptionClass(): string;
}
