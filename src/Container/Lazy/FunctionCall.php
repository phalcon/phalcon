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

namespace Phalcon\Container\Lazy;

use Phalcon\Container\Container;

use function call_user_func_array;

class FunctionCall extends AbstractLazy
{
    /**
     * @param string $function
     * @param array  $arguments
     */
    public function __construct(
        protected string $function,
        protected array $arguments
    ) {
    }

    /**
     * @param Container $container
     *
     * @return mixed
     */
    public function __invoke(Container $container): mixed
    {
        $arguments = $this->resolveArguments($container, $this->arguments);

        return call_user_func_array($this->function, $arguments);
    }
}
