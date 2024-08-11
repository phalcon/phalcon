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

namespace Phalcon\Tests\Unit\Mvc\Micro\Collection;

use Phalcon\Mvc\Micro;
use Phalcon\Mvc\Micro\Collection;
use Phalcon\Tests\Controllers\Micro\Collections\PersonasLazyController;
use Phalcon\Tests\AbstractUnitTestCase;

class SetHandlerTest extends AbstractUnitTestCase
{
    public function testMicroCollectionsLazy(): void
    {
        $app        = new Micro();
        $collection = new Collection();

        $collection->setHandler(
            PersonasLazyController::class,
            true
        );


        $collection->map('/', 'index');
        $collection->map('/edit/{number}', 'edit');

        $app->mount($collection);


        $app->handle('/');

        $this->assertEquals(
            1,
            PersonasLazyController::getEntered()
        );


        $app->handle('/edit/100');

        $this->assertEquals(
            101,
            PersonasLazyController::getEntered()
        );
    }

    /**
     * Tests Phalcon\Mvc\Micro\Collection :: setHandler()
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2018-11-13
     */
    public function testMvcMicroCollectionSetHandler(): void
    {
        $this->markTestSkipped('Need implementation');
    }
}
