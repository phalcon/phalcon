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

use function is_array;

trait ConfigTrait
{
    /**
     * @param array|ConfigInterface $config
     *
     * @return array
     */
    protected function checkConfig(array | ConfigInterface $config): array
    {
        if ($config instanceof ConfigInterface) {
            return $config->toArray();
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
                "You must provide the '" . $element . "' option in the factory config parameter."
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
