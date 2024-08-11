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

use Phalcon\Mvc\View\Engine\Php;
use Phalcon\Mvc\View\Engine\Volt;
use Phalcon\Tests\Fixtures\Mvc\View\Engine\Mustache;
use Phalcon\Tests\Fixtures\Mvc\View\Engine\Twig;
use Phalcon\Tests\Fixtures\Traits\DiTrait;
use Phalcon\Tests\AbstractUnitTestCase;

class GetRegisteredEnginesTest extends AbstractUnitTestCase
{
    use DiTrait;

    /**
     * Tests the Simple::getRegisteredEngines
     *
     * @author Kamil Skowron <git@hedonsoftware.com>
     * @since  2014-05-28
     */
    public function testGetRegisteredEngines(): void
    {
        $this->newDi();
        $this->setDiService('viewSimple');

        $view = $this->container->get('viewSimple');

        $engines = [
            '.mhtml' => Mustache::class,
            '.phtml' => Php::class,
            '.twig'  => Twig::class,
            '.volt'  => Volt::class,
        ];

        $view->registerEngines($engines);

        $this->assertEquals(
            $engines,
            $view->getRegisteredEngines()
        );
    }
}
