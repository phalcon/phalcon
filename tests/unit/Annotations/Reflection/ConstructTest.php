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

final class ConstructTest extends UnitTestCase
{
    /**
     * Tests Phalcon\Annotations\Reflection :: __construct()
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2020-02-21
     */
    public function testAnnotationsReflectionConstruct(): void
    {
        $reflection = new Reflection();

        $this->assertInstanceOf(Reflection::class, $reflection);
    }

    /**
     * Tests parsing final class annotations
     *
     * @return void
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2016-01-26
     */
    public function testClassAnnotations(): void
    {
        $reader = new Reader();

        $reflection = new Reflection(
            $reader->parse('AnnotationsTestClass')
        );

        $constantsAnnotations = $reflection->getConstantsAnnotations();
        $this->assertIsArray($constantsAnnotations);

        $annotations = $constantsAnnotations['TEST_CONST1'];

        $expected = Collection::class;
        $actual   = $annotations;
        $this->assertInstanceOf($expected, $actual);

        $actual = $annotations->has('Simple');
        $this->assertTrue($actual);

        $methodsAnnotations = $reflection->getMethodsAnnotations();

        $this->assertIsArray($methodsAnnotations);

        $expected = Collection::class;
        $actual   = $methodsAnnotations['testMethod1'];
        $this->assertInstanceOf($expected, $actual);

        $total = 0;

        foreach ($methodsAnnotations as $method => $annotations) {
            $this->assertIsString($method);

            $number = 0;

            foreach ($annotations as $annotation) {
                $expected = Annotation::class;
                $actual   = $annotation;
                $this->assertInstanceOf($expected, $actual);

                $number++;
                $total++;
            }

            $this->assertGreaterThan(0, $number);
        }

        $expected = 14;
        $actual   = $total;
        $this->assertSame($expected, $actual);

        /** @var Collection $annotations */
        $annotations = $methodsAnnotations['testMethod1'];

        $actual = $annotations->has('Simple');
        $this->assertTrue($actual);

        $actual = $annotations->has('NoSimple');
        $this->assertFalse($actual);

        $annotation = $annotations->get('Simple');

        $expected = 'Simple';
        $actual   = $annotation->getName();
        $this->assertSame($expected, $actual);

        $expected = [];
        $actual   = $annotation->getArguments();
        $this->assertSame($expected, $actual);

        $expected = 0;
        $actual   = $annotation->numberArguments();
        $this->assertSame($expected, $actual);


        $actual = $annotation->hasArgument('none');
        $this->assertFalse($actual);

        $annotation = $annotations->get('NamedMultipleParams');
        $expected   = 'NamedMultipleParams';
        $actual     = $annotation->getName();
        $this->assertSame($expected, $actual);

        $expected = 2;
        $actual   = $annotation->numberArguments();
        $this->assertSame($expected, $actual);

        $expected = ['first' => 'First', 'second' => 'Second'];
        $actual   = $annotation->getArguments();
        $this->assertSame($expected, $actual);

        $actual = $annotation->hasArgument('first');
        $this->assertTrue($actual);

        $this->assertSame('First', $annotation->getArgument('first'));

        $actual = $annotation->hasArgument('none');
        $this->assertFalse($actual);

        $propertiesAnnotations = $reflection->getPropertiesAnnotations();
        $this->assertIsArray($propertiesAnnotations);

        $expected = Collection::class;
        $actual   = $propertiesAnnotations['testProp1'];
        $this->assertInstanceOf($expected, $actual);

        $total = 0;

        foreach ($propertiesAnnotations as $annotations) {
            $expected = Collection::class;
            $actual   = $propertiesAnnotations['testProp1'];
            $this->assertInstanceOf($expected, $actual);

            $number = 0;

            foreach ($annotations as $annotation) {
                $expected = Annotation::class;
                $actual   = $annotation;
                $this->assertInstanceOf($expected, $actual);

                $number++;
                $total++;
            }

            $this->assertGreaterThan(0, $number);
        }

        $expected = 10;
        $actual   = $total;
        $this->assertSame($expected, $actual);
    }

    /**
     * Executed before each test.
     *
     * @return void
     */
    protected function setUp(): void
    {
        require_once dataDir('fixtures/Annotations/AnnotationsTestClass.php');
    }
}
