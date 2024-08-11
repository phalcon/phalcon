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

use Phalcon\Application\Exception;
use Phalcon\Tests\Fixtures\Application\ApplicationFixture;
use Phalcon\Tests\AbstractUnitTestCase;

final class GetRegisterModulesTest extends AbstractUnitTestCase
{
    /**
     * Tests Phalcon\Acl\Role :: getModule()
     *
     * @return void
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2020-09-09
     */
    public function testApplicationGetModule(): void
    {
        $application = new ApplicationFixture();

        $modules = [
            'admin'    => [1],
            'invoices' => [2],
        ];
        $application->registerModules($modules);

        $expected = [1];
        $actual   = $application->getModule('admin');
        $this->assertSame($expected, $actual);
    }

    /**
     * Tests Phalcon\Acl\Role :: getModule() - exception
     *
     * @return void
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2020-09-09
     */
    public function testApplicationGetModuleException(): void
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage(
            "Module 'no-module' is not registered in the application container"
        );

        $application = new ApplicationFixture();
        $application->getModule('no-module');
    }

    /**
     * Tests Phalcon\Application\* :: registerModules()
     *
     * @return void
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2020-09-09
     */
    public function testApplicationRegisterModules(): void
    {
        $application = new ApplicationFixture();

        $modules = [
            'admin'    => [1],
            'invoices' => [2],
        ];
        $application->registerModules($modules);

        $expected = $modules;
        $actual   = $application->getModules();
        $this->assertSame($expected, $actual);
    }

    /**
     * Tests Phalcon\Acl\Role :: registerModules() - merge
     *
     * @return void
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2020-09-09
     */
    public function testApplicationRegisterModulesMerge(): void
    {
        $application = new ApplicationFixture();

        $modules1 = [
            'admin'    => [1],
            'invoices' => [2],
        ];
        $application->registerModules($modules1);

        $modules2 = [
            'moderator' => [3],
            'posts'     => [4],
        ];
        $application->registerModules($modules2);

        $expected = $modules2;
        $actual   = $application->getModules();
        $this->assertSame($expected, $actual);

        $application->registerModules($modules1, true);

        $expected = array_merge($modules2, $modules1);
        $actual   = $application->getModules();
        $this->assertSame($expected, $actual);
    }
}
