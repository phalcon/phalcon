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
            throw new Exception(
                'Config must be array or Phalcon\Config object'
            );
        }

        if (true !== isset($config['adapter'])) {
            throw new Exception(
                'You must provide "adapter" option in factory config parameter.'
            );
        }

        return $config;
    }
}
