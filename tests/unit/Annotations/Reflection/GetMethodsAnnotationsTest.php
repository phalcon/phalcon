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

namespace Phalcon\Tests\Unit\Annotations\Reflection;

use Phalcon\Annotations\Collection;
use Phalcon\Annotations\Reader;
use Phalcon\Annotations\Reflection;
use Phalcon\Tests\AbstractUnitTestCase;

final class GetMethodsAnnotationsTest extends AbstractUnitTestCase
{
    /**
     * Tests Phalcon\Annotations\Reflection :: getMethodsAnnotations()
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2020-02-21
     */
    public function testAnnotationsReflectionGetMethodsAnnotations(): void
    {
        $reader = new Reader();

        $reflection = new Reflection(
            $reader->parse('AnnotationsTestClass')
        );

        $methodsAnnotations = $reflection->getMethodsAnnotations();
        $this->assertIsArray($methodsAnnotations);

        $number = 0;

        foreach ($methodsAnnotations as $annotation) {
            $expected = Collection::class;
            $actual   = $annotation;
            $this->assertInstanceOf($expected, $actual);

            $number++;
        }

        $expected = 4;
        $actual   = $number;
        $this->assertSame($expected, $actual);

        $expected = 4;
        $actual   = $methodsAnnotations;
        $this->assertCount($expected, $actual);
    }

    /**
     * Tests creating empty Reflection object
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2016-01-26
     */
    public function testEmptyReflection(): void
    {
        $reflection = new Reflection();

        $actual = $reflection->getMethodsAnnotations();
        $this->assertIsArray($actual);
        $this->assertEmpty($actual);
    }

    /**
     * executed before each test
     */
    protected function setUp(): void
    {
        require_once dataDir('fixtures/Annotations/AnnotationsTestClass.php');
    }
}
