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

namespace Phalcon\Tests\Unit\Mvc\Application;

use Phalcon\Mvc\Application;
use Phalcon\Tests\Modules\Frontend\Module;
use Phalcon\Tests\AbstractUnitTestCase;

class GetModulesTest extends AbstractUnitTestCase
{
    /**
     * Tests Phalcon\Mvc\Application :: getModules()
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2018-11-13
     *
     * @author Nathan Edwards <https://github.com/npfedwards>
     * @since  2018-12-26
     */
    public function testMvcApplicationGetModules(): void
    {
        $application = new Application();

        $definition = [
            'frontend' => [
                'className' => Module::class,
                'path'      => dataDir('fixtures/modules/frontend/Module.php'),
            ],
            'backend'  => [
                'className' => \Phalcon\Tests\Modules\Backend\Module::class,
                'path'      => dataDir('fixtures/modules/backend/Module.php'),
            ],
        ];

        $application->registerModules($definition);

        $this->assertEquals(
            $definition,
            $application->getModules()
        );
    }

    /**
     * Tests Phalcon\Mvc\Application :: getModules() - empty
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2018-11-13
     *
     * @author Nathan Edwards <https://github.com/npfedwards>
     * @since  2018-12-26
     */
    public function testMvcApplicationGetModulesEmpty(): void
    {
        $application = new Application();

        $this->assertEquals(
            [],
            $application->getModules()
        );
    }
}
