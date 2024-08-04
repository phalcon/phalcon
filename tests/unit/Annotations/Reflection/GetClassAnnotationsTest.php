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

use Phalcon\Annotations\Annotation;
use Phalcon\Annotations\Collection;
use Phalcon\Annotations\Reader;
use Phalcon\Annotations\Reflection;
use Phalcon\Tests\UnitTestCase;

final class GetClassAnnotationsTest extends UnitTestCase
{
    /**
     * Tests creating empty Reflection object
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2016-01-26
     */
    public function testEmptyReflection(): void
    {
        $reflection = new Reflection();

        $actual = $reflection->getClassAnnotations();
        $this->assertNull($actual);
    }

    /**
     * Tests parsing a real class
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2016-01-26
     */
    public function testParsingARealClass(): void
    {
        $reader = new Reader();

        $reflection = new Reflection(
            $reader->parse('AnnotationsTestClass')
        );

        $classAnnotations = $reflection->getClassAnnotations();

        $expected = Collection::class;
        $actual   = $classAnnotations;
        $this->assertInstanceOf($expected, $actual);

        $number = 0;

        foreach ($classAnnotations as $annotation) {
            $expected = Annotation::class;
            $actual   = $annotation;
            $this->assertInstanceOf($expected, $actual);

            $number++;
        }

        $expected = 9;
        $actual   = $number;
        $this->assertSame($expected, $actual);

        $expected = 9;
        $actual   = $classAnnotations;
        $this->assertCount($expected, $actual);
    }

    /**
     * executed before each test
     */
    protected function setUp(): void
    {
        require_once dataDir('fixtures/Annotations/AnnotationsTestClass.php');
    }
}
