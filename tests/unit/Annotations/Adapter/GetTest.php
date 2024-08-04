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

namespace Phalcon\Tests\Unit\Annotations\Adapter;

use AnnotationsTestClass;
use Phalcon\Annotations\Collection;
use Phalcon\Annotations\Reflection;
use Phalcon\Tests\Fixtures\Traits\AnnotationsTrait;
use Phalcon\Tests\UnitTestCase;

final class GetTest extends UnitTestCase
{
    use AnnotationsTrait;

    /**
     * Tests Phalcon\Annotations\Adapter :: get()
     *
     * @dataProvider getExamples
     *
     * @author       Phalcon Team <team@phalcon.io>
     * @since        2022-12-30
     */
    public function testAnnotationsAdapterGet(
        string $class,
        array $params
    ): void {
        require_once dataDir('fixtures/Annotations/AnnotationsTestClass.php');

        $adapter = new $class($params);

        $classAnnotations = $adapter->get(AnnotationsTestClass::class);

        $actual = is_object($classAnnotations);
        $this->assertTrue($actual);

        $expected = Reflection::class;
        $actual   = $classAnnotations;
        $this->assertInstanceOf($expected, $actual);

        $expected = Collection::class;
        $actual   = $classAnnotations->getClassAnnotations();
        $this->assertInstanceOf($expected, $actual);
    }
}
