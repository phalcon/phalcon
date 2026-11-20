<?php

/**
 * This file is part of the Phalcon Framework.
 *
 * (c) Phalcon Team <team@phalcon.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Phalcon\Tests\Support\Controllers;

use Phalcon\Annotations\Router\Get;
use Phalcon\Annotations\Router\RoutePrefix;

/**
 * The route prefix is an int, as in a configuration file.
 *
 * @RoutePrefix(2024)
 */
#[RoutePrefix(2024)]
class WeakTypesPrefixIntController
{
    /**
     * @Get("/")
     */
    #[Get("/")]
    public function indexAction()
    {
    }
}
