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

namespace Phalcon\Tests\Unit\Annotations\AnnotationsFactory;

use Phalcon\Annotations\Adapter\Apcu;
use Phalcon\Annotations\Adapter\Memory;
use Phalcon\Annotations\Adapter\Stream;
use Phalcon\Annotations\AnnotationsFactory;
use Phalcon\Annotations\Exception;
use Phalcon\Tests\UnitTestCase;

final class NewInstanceTest extends UnitTestCase
{
    public static function getExamples(): array
    {
        return [
            [
                'apcu',
                Apcu::class,
            ],
            [
                'memory',
                Memory::class,
            ],
            [
                'stream',
                Stream::class,
            ],
        ];
    }

    /**
     * Tests Phalcon\Annotations\AdapterFactory :: newInstance()
     *
     * @dataProvider getExamples
     *
     * @author       Phalcon Team <team@phalcon.io>
     * @since        2019-05-19
     */
    public function testAnnotationsAdapterFactoryNewInstance(
        string $name,
        string $className
    ): void {
        $factory = new AnnotationsFactory();
        $class   = $factory->newInstance($name);

        $this->assertInstanceOf($className, $class);
    }

    /**
     * Tests Phalcon\Translate\InterpolatorFactory :: newInstance() - exception
     *
     *
     * @author       Phalcon Team <team@phalcon.io>
     * @since        2020-09-09
     */
    public function testAnnotationsAdapterFactoryNewInstanceException(): void
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Service unknown is not registered');

        $adapter = new AnnotationsFactory();
        $adapter->newInstance('unknown');
    }
}
