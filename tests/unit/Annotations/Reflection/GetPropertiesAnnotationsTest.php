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
use Phalcon\Tests\UnitTestCase;

final class GetPropertiesAnnotationsTest extends UnitTestCase
{
    /**
     * Tests Phalcon\Annotations\Reflection :: getPropertiesAnnotations()
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2020-02-21
     */
    public function testAnnotationsReflectionGetPropertiesAnnotations(): void
    {
        $reader = new Reader();

        $reflection = new Reflection(
            $reader->parse('AnnotationsTestClass')
        );

        $propertiesAnnotations = $reflection->getPropertiesAnnotations();

        $this->assertIsArray($propertiesAnnotations);

        $number = 0;

        foreach ($propertiesAnnotations as $annotation) {
            $expected = Collection::class;
            $actual   = $annotation;
            $this->assertInstanceOf($expected, $actual);

            $number++;
        }

        $expected = 3;
        $actual   = $number;
        $this->assertSame($expected, $actual);

        $expected = 3;
        $actual   = $propertiesAnnotations;
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

        $actual = $reflection->getPropertiesAnnotations();
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
