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
use Phalcon\Mvc\View\Engine\Php;
use Phalcon\Mvc\View\Engine\Volt;
use Phalcon\Tests\Fixtures\Traits\DiTrait;
use Phalcon\Tests\AbstractUnitTestCase;

class RegisterEnginesTest extends AbstractUnitTestCase
{
    use DiTrait;

    public function setUp(): void
    {
        $this->newDi();
        $this->setDiService('view');
    }

    /**
     * Tests the View::registerEngines
     *
     * @author Kamil Skowron <git@hedonsoftware.com>
     * @since  2014-05-28
     */
    public function testMvcViewRegisterEngines(): void
    {
        $engines = $this->getViewRegisterEngines();

        /** @var View $view */
        $view = $this->getService('view');

        $view->registerEngines($engines);

        $this->assertEquals(
            $engines,
            $view->getRegisteredEngines()
        );
    }

    private function getViewRegisterEngines(): array
    {
        return [
            '.phtml' => Php::class,
            '.volt'  => Volt::class,
        ];
    }
}
