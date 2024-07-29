<?php

/*
 * This file is part of the Phalcon Framework.
 *
 * (c) Phalcon Team <team@phalcon.io>
 *
 * For the full copyright and license information, please view the LICENSE.txt
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Phalcon\Factory;

use Phalcon\Config\ConfigInterface;

abstract class AbstractConfigFactory
{
    /**
     * Checks the config if it is a valid object\
     *
     * @param mixed $config
     *
     * @throws Exception
     * @return array
     */
    protected function checkConfig(mixed $config): array
    {
        if ($config instanceof ConfigInterface) {
            $config = $config->toArray();
        }

        if (is_array($config) === false) {
            throw $this->getException(
                "Config must be array or Phalcon\\Config\\Config object"
            );
        }

        return $config;
    }

    /**
     * Checks if the config has "adapter"
     *
     * @param array $config
     * @param string $element
     *
     * @throws Exception
     * @return array
     */
    protected function checkConfigElement(array $config, string $element): array
    {
        if (!isset($config[$element])) {
            throw $this->getException(
                "You must provide '$element' option in factory config parameter."
            );
        }

        return $config;
    }

    /**
     * Returns the exception object for the child class
     *
     * @param string $message
     *
     * @return Exception
     */
    protected function getException(string $message): Exception
    {
        return new Exception($message);
    }
}
