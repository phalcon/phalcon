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

use function is_array;

abstract class AbstractConfigFactory
{
    /**
     * Checks the config if it is a valid object
     *
     * @param array<string, mixed>|ConfigInterface $config
     *
     * @return array<string, mixed>
     * @throws BaseException
     */
    protected function checkConfig(mixed $config): array
    {
        if ($config instanceof ConfigInterface) {
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
     * @param string               $element
     *
     * @return array<string, mixed>
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
     *
     * @param string $message
     *
     * @return BaseException
     */
    protected function getException(string $message): BaseException
    {
        $exception = $this->getExceptionClass();

        return new $exception($message);
    }

    /**
     * @return string
     */
    protected function getExceptionClass(): string
    {
        return BaseException::class;
    }
}
