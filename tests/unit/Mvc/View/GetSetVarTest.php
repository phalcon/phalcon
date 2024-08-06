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

namespace Phalcon\Tests\Unit\Mvc\View;

use Phalcon\Mvc\View;
use Phalcon\Tests\Fixtures\Traits\ViewTrait;
use Phalcon\Tests\UnitTestCase;

class GetSetVarTest extends UnitTestCase
{
    use ViewTrait;

    /**
     * Tests Phalcon\Mvc\View :: getVar() / setVar()
     */
    public function testMvcViewGetSetVar(): void
    {
        $view = new View();

        $view->setVar('foo1', 'bar1');

        $this->assertEquals(
            'bar1',
            $view->getVar('foo1')
        );
    }
}
