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
use Phalcon\Support\Exception;

use function is_array;
use function is_object;

/**
 * Trait ConfigTrait
 *
 * @package Phalcon\Support\Traits
 */
trait ConfigTrait
{
    /**
     * @param mixed $config
     *
     * @return array
     * @throws Exception
     */
    protected function checkConfig($config): array
    {
        if (true === is_object($config) && $config instanceof ConfigInterface) {
            return $config->toArray();
        }

        if (true !== is_array($config)) {
            $exception = $this->getExceptionClass();
            throw new $exception(
                'Config must be array or Phalcon\Config\Config object'
            );
        }
//
//        if (true !== isset($config['adapter'])) {
//            $exception = $this->getExceptionClass();
//            throw new $exception(
//                'You must provide "adapter" option in factory config parameter.'
//            );
//        }

        return $config;
    }

    /**
     * Checks if the config has a specific element
     *
     * @param array  $config
     * @param string $element
     *
     * @return array
     */
    protected function checkConfigElement(array $config, string $element): array
    {
        if (true !== isset($config[$element])) {
            $exception = $this->getExceptionClass();
            throw new $exception(
                "You must provide '" . $element . "' option in factory config parameter."
            );
        }

        return $config;
    }

    /**
     * Returns the exception class for the factory
     *
     * @return string
     */
    abstract protected function getExceptionClass(): string;
}
