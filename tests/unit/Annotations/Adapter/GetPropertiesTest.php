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
use Phalcon\Tests\AbstractUnitTestCase;

use function array_keys;
use function outputDir;

final class GetPropertiesTest extends AbstractUnitTestCase
{
    use AnnotationsTrait;

    /**
     * Tests Phalcon\Annotations\Adapter :: getProperties()
     *
     * @dataProvider getExamples
     *
     * @author       Phalcon Team <team@phalcon.io>
     * @since        2022-12-30
     */
    public function testAnnotationsAdapterGetProperties(
        string $class,
        array $params
    ): void {
        require_once dataDir('fixtures/Annotations/AnnotationsTestClass.php');

        $adapter = new $class($params);

        $propertyAnnotations = $adapter->getProperties(AnnotationsTestClass::class);

        $keys     = array_keys($propertyAnnotations);
        $expected = [
            'testProp1',
            'testProp3',
            'testProp4',
        ];
        $actual   = $keys;
        $this->assertSame($expected, $actual);

        foreach ($propertyAnnotations as $propertyAnnotation) {
            $expected = Collection::class;
            $actual   = $propertyAnnotation;
            $this->assertInstanceOf($expected, $actual);
        }

        $this->safeDeleteFile(outputDir('tests/annotations/testclass.php'));
    }
}
