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
use Phalcon\Tests\AbstractUnitTestCase;

class UnderscoreGetSetTest extends AbstractUnitTestCase
{
    /**
     * Tests Phalcon\Mvc\View\Simple :: __get()/__set()
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2018-11-13
     */
    public function testMvcViewSimpleUnderscoreGetSet(): void
    {
        $view = new Simple();

        $view->foo = 'bar';

        $this->assertEquals(
            'bar',
            $view->foo
        );
    }
}
