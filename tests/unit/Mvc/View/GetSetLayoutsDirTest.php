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
use Phalcon\Tests\AbstractUnitTestCase;

class GetSetLayoutsDirTest extends AbstractUnitTestCase
{
    use ViewTrait;

    /**
     * Tests Phalcon\Mvc\View :: getLayoutsDir() / setLayoutsDir()
     */
    public function testMvcViewGetSetLayoutsDir(): void
    {
        $view = new View();

        $view->setBasePath(
            dataDir()
        );

        $view->setLayoutsDir('views/layouts/');

        $this->assertEquals(
            'views/layouts/',
            $view->getLayoutsDir()
        );
    }
}
