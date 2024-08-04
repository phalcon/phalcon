<?php

/**
 * This file is part of the Phalcon Framework.
 *
 * (c) Phalcon Team <team@phalcon.io>
 *
 * For the full copyright and license information, please view the
 * LICENSE.txt file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Phalcon\Tests\Unit\Translate\InterpolatorFactory;

use Codeception\Example;
use Phalcon\Translate\Exception;
use Phalcon\Translate\Interpolator\AssociativeArray;
use Phalcon\Translate\Interpolator\IndexedArray;
use Phalcon\Translate\InterpolatorFactory;
use Phalcon\Tests\UnitTestCase;

final class NewInstanceTest extends UnitTestCase
{
    /**
     * Tests Phalcon\Translate\InterpolatorFactory :: newInstance()
     *
     * @return void
     *
     * @dataProvider getExamples
     *
     * @author       Phalcon Team <team@phalcon.io>
     * @since        2020-09-09
     */
    public function testTranslateInterpolatorFactoryNewInstance(
        string $name,
        string $class
    ): void {
        $adapter = new InterpolatorFactory();
        $service = $adapter->newInstance($name);

        $this->assertInstanceOf($class, $service);
    }

    /**
     * Tests Phalcon\Translate\InterpolatorFactory :: newInstance() - exception
     *
     *
     * @author       Phalcon Team <team@phalcon.io>
     * @since        2020-09-09
     */
    public function testTranslateInterpolatorFactoryNewInstanceException(): void
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Service unknown is not registered');

        $adapter = new InterpolatorFactory();
        $adapter->newInstance('unknown');
    }

    /**
     * @return string[][]
     */
    public static function getExamples(): array
    {
        return [
            ['associativeArray', AssociativeArray::class],
            ['indexedArray', IndexedArray::class],
        ];
    }
}
