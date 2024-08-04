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
use Phalcon\Tests\Fixtures\Traits\AnnotationsTrait;
use Phalcon\Tests\UnitTestCase;

use function outputDir;
use function safeDeleteFile;

final class GetMethodTest extends UnitTestCase
{
    use AnnotationsTrait;

    /**
     * Tests Phalcon\Annotations\Adapter :: getMethod()
     *
     * @dataProvider getExamples
     *
     * @author       Phalcon Team <team@phalcon.io>
     * @since        2022-12-30
     */
    public function testAnnotationsAdapterGetMethod(
        string $class,
        array $params
    ): void {
        require_once dataDir('fixtures/Annotations/AnnotationsTestClass.php');

        $adapter          = new $class($params);
        $methodAnnotation = $adapter->getMethod(
            AnnotationsTestClass::class,
            'testMethod1'
        );

        $expected = Collection::class;
        $actual   = $methodAnnotation;
        $this->assertInstanceOf($expected, $actual);

        $adapter          = new $class($params);
        $methodAnnotation = $adapter->getMethod(
            AnnotationsTestClass::class,
            'unknownMethod'
        );

        $expected = Collection::class;
        $actual   = $methodAnnotation;
        $this->assertInstanceOf($expected, $actual);

        $this->safeDeleteFile(outputDir('tests/annotations/testclass.php'));
    }
}
