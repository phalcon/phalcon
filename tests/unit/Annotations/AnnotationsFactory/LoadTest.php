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
use Phalcon\Annotations\AnnotationsFactory;
use Phalcon\Config\Config;
use Phalcon\Tests\Fixtures\Traits\FactoryTrait;
use Phalcon\Tests\UnitTestCase;

final class LoadTest extends UnitTestCase
{
    use FactoryTrait;

    public function setUp(): void
    {
        $this->init();
    }

    /**
     * Tests Phalcon\Annotations\AnnotationsFactory :: load()
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2019-05-18
     */
    public function testAnnotationsAdapterFactoryLoad(): void
    {
        $options = $this->config->annotations;
        $this->runTests($options);
    }

    /**
     * Tests Phalcon\Annotations\AnnotationsFactory :: load()
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2019-05-18
     */
    public function testAnnotationsAdapterFactoryLoadArray(): void
    {
        $options = $this->arrayConfig['annotations'];
        $this->runTests($options);
    }

    private function runTests(array | Config $options)
    {
        $factory = new AnnotationsFactory();
        $adapter = $factory->load($options);

        $this->assertInstanceOf(Apcu::class, $adapter);
    }
}
