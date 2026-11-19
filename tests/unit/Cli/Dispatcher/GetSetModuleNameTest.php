<?php

/**
 * This file is part of the Phalcon Framework.
 *
 * (c) Phalcon Team <team@phalcon.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Phalcon\Tests\Unit\Cli\Dispatcher;

use Phalcon\Cli\Dispatcher;
use Phalcon\Talon\PHPUnit\AbstractUnitTestCase;
use TypeError;

/**
 * Class GetSetModuleNameTest extends AbstractUnitTestCase
 */
final class GetSetModuleNameTest extends AbstractUnitTestCase
{
    /**
     * @author Phalcon Team <team@phalcon.io>
     * @since  2018-11-13
     */
    public function testCliDispatcherGetModuleName(): void
    {
        $dispatcher = new Dispatcher();

        $expected = '';
        $actual   = $dispatcher->getModuleName();
        $this->assertSame($expected, $actual);

        $moduleName = "Phalcon";
        $dispatcher->setModuleName($moduleName);

        $expected = $moduleName;
        $actual   = $dispatcher->getModuleName();
        $this->assertSame($expected, $actual);
    }

    /**
     * The module name is a string, thus null gives a TypeError, as in
     * cphalcon.
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2026-11-19
     *
     * @issue  https://github.com/phalcon/phalcon/issues/840
     */
    public function testCliDispatcherSetModuleNameNull(): void
    {
        $dispatcher = new Dispatcher();

        $this->expectException(TypeError::class);
        $this->expectExceptionMessage(
            'Cannot assign null to property '
            . 'Phalcon\Dispatcher\AbstractDispatcher::$moduleName of type string'
        );

        $dispatcher->setModuleName(null);
    }
}
