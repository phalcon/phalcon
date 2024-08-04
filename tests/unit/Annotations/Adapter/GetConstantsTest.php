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

use function array_keys;
use function outputDir;
use function safeDeleteFile;

final class GetConstantsTest extends UnitTestCase
{
    use AnnotationsTrait;

    /**
     * Tests Phalcon\Annotations\Adapter :: getMethods()
     *
     * @dataProvider getExamples
     *
     * @author       Phalcon Team <team@phalcon.io>
     * @since        2022-12-30
     */
    public function testAnnotationsAdapterGetMethods(
        string $class,
        array $params
    ): void {
        require_once dataDir('fixtures/Annotations/AnnotationsTestClass.php');

        $adapter = new $class($params);

        $methodConstants = $adapter->getConstants(
            AnnotationsTestClass::class
        );

        $keys     = array_keys($methodConstants);
        $expected = [
            'TEST_CONST1',
        ];
        $actual   = $keys;
        $this->assertSame($expected, $actual);

        foreach ($methodConstants as $methodConstant) {
            $expected = Collection::class;
            $actual   = $methodConstant;
            $this->assertInstanceOf($expected, $actual);
        }

        $this->safeDeleteFile(outputDir('tests/annotations/testclass.php'));
    }
}
