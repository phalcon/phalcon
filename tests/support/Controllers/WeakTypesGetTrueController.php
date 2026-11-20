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

/**
 * The route of the action is the bool true.
 */
class WeakTypesGetTrueController
{
    /**
     * @Get(true)
     */
    public function indexAction()
    {
    }
}
