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

namespace Phalcon\Tests\Unit\Di\Injectable;

use Exception;
use Phalcon\Di\Di;
use Phalcon\Tests\Fixtures\Di\InjectableComponent;
use Phalcon\Tests\AbstractUnitTestCase;
use stdClass;

use function spl_object_hash;

class UnderscoreGetTest extends AbstractUnitTestCase
{
    /**
     * Unit Tests Phalcon\Di\Injectable :: __get() - exception
     *
     * @return void
     *
     * @throws Exception
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2019-09-09
     */
    public function testDiInjectableUnderscoreGetException(): void
    {
        if (PHP_OS_FAMILY === 'Windows') {
            $this->markTestSkipped('Need to fix Windows new lines...');
        }

        Di::reset();
        $container = new Di();

        $container->set('component', InjectableComponent::class);

        $component = $container->get('component');

        $expected = 'Access to undefined property unknown';
        $actual   = '';
        try {
            $result = $component->unknown;
        } catch (Exception $ex) {
            $actual = $ex->getMessage();
        }
        $this->assertStringContainsString($expected, $actual);
    }

    /**
     * Unit Tests Phalcon\Di\Injectable :: __get()/__isset()
     *
     * @return void
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2019-09-09
     */
    public function testDiInjectableUnderscoreGetIsset(): void
    {
        Di::reset();
        $container = new Di();

        $stdClass = function () {
            return new stdClass();
        };

        $container->set('std', $stdClass);
        $container->set('component', InjectableComponent::class);

        $component = $container->get('component');
        $actual    = $component->getDI();
        $this->assertSame($container, $actual);

        $class  = stdClass::class;
        $actual = $component->std;
        $this->assertInstanceOf($class, $actual);

        $expected = spl_object_hash($container);
        $actual   = spl_object_hash($component->di);
        $this->assertSame($expected, $actual);

        $actual = isset($component->di);
        $this->assertTrue($actual);

        $actual = isset($component->component);
        $this->assertTrue($actual);

        $actual = isset($component->std);
        $this->assertTrue($actual);
    }
}
