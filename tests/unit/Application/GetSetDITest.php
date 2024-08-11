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

namespace Phalcon\Tests\Unit\Application;

use Phalcon\Di\FactoryDefault;
use Phalcon\Tests\Fixtures\Application\ApplicationFixture;
use Phalcon\Tests\AbstractUnitTestCase;

use function spl_object_hash;

final class GetSetDITest extends AbstractUnitTestCase
{
    /**
     * Tests Phalcon\Acl\Role :: getDI()/setDI()
     *
     * @return void
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2020-09-09
     */
    public function testApplicationGetSetDi(): void
    {
        $container   = new FactoryDefault();
        $application = new ApplicationFixture();

        $application->setDI($container);
        $actual = $application->getDI();
        $this->assertSame(spl_object_hash($container), spl_object_hash($actual));
    }

    /**
     * Tests Phalcon\Application\* :: getDI()/setDI() - construct
     *
     * @return void
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2020-09-09
     */
    public function testApplicationGetSetDiConstruct(): void
    {
        $container   = new FactoryDefault();
        $application = new ApplicationFixture($container);

        $actual = $application->getDI();
        $this->assertSame(spl_object_hash($container), spl_object_hash($actual));
    }
}
