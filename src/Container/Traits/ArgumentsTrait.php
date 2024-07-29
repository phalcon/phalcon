<?php

/**
 * This file is part of the Phalcon Framework.
 *
 * (c) Phalcon Team <team@phalcon.io>
 *
 * For the full copyright and license information, please view the LICENSE.txt
 * file that was distributed with this source code.
 *
 * Implementation of this file has been influenced by CapsulePHP
 *
 * @link    https://github.com/capsulephp/di
 * @license https://github.com/capsulephp/di/blob/3.x/LICENSE.md
 */

declare(strict_types=1);

namespace Phalcon\Container\Traits;

use Phalcon\Container\Container;
use Phalcon\Container\Lazy\AbstractLazy;

trait ArgumentsTrait
{
    /**
     * @param Container $container
     * @param mixed     $argument
     *
     * @return mixed
     */
    public function resolveArgument(
        Container $container,
        mixed $argument
    ): mixed {
        if ($argument instanceof AbstractLazy) {
            return $argument($container);
        }

        return $argument;
    }

    /**
     * @param Container $container
     * @param array     $arguments
     *
     * @return array
     */
    public function resolveArguments(
        Container $container,
        array $arguments
    ): array {
        $return = [];

        foreach ($arguments as $key => $value) {
            $return[$key] = $this->resolveArgument($container, $value);
        }

        return $return;
    }
}
