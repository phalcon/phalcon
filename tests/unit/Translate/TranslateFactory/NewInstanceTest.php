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

namespace Phalcon\Tests\Unit\Translate\TranslateFactory;

use Phalcon\Tests\Fixtures\Traits\TranslateCsvTrait;
use Phalcon\Translate\Adapter\AdapterInterface;
use Phalcon\Translate\Adapter\Csv;
use Phalcon\Translate\Exception;
use Phalcon\Translate\InterpolatorFactory;
use Phalcon\Translate\TranslateFactory;
use Phalcon\Tests\UnitTestCase;

use function uniqid;

final class NewInstanceTest extends UnitTestCase
{
    use TranslateCsvTrait;

    /**
     * Tests Phalcon\Translate\TranslateFactory :: newInstance()
     *
     * @return void
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2020-09-09
     * `     */
    public function testTranslateTranslateFactoryNewInstance(): void
    {
        $interpolator = new InterpolatorFactory();
        $factory      = new TranslateFactory($interpolator);
        $language     = $this->getCsvConfig()['ru'];
        $adapter      = $factory->newInstance('csv', $language);

        $this->assertInstanceOf(Csv::class, $adapter);
        $this->assertInstanceOf(AdapterInterface::class, $adapter);
    }

    /**
     * Tests Phalcon\Translate\TranslateFactory :: newInstance() - exception
     *
     * @return void
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2020-09-09
     */
    public function testTranslateTranslateFactoryNewInstanceException(): void
    {
        $name = uniqid('service-');
        $this->expectException(Exception::class);
        $this->expectExceptionMessage(
            'Service ' . $name . ' is not registered'
        );

        $interpolator = new InterpolatorFactory();
        $factory      = new TranslateFactory($interpolator);
        $factory->newInstance($name);
    }
}
