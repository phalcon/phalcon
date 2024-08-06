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

namespace Phalcon\Tests\Unit\Mvc\View\Simple;

use Phalcon\Mvc\View\Simple;
use Phalcon\Mvc\View\ViewBaseInterface;
use Phalcon\Tests\UnitTestCase;


class ConstructTest extends UnitTestCase
{
    /**
     * Tests Phalcon\Mvc\View\Simple :: __construct()
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2018-11-13
     */
    public function testMvcViewSimpleConstruct(): void
    {
        $view = new Simple();
        $this->assertInstanceOf(ViewBaseInterface::class, $view);
    }
}
