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

namespace Phalcon\Tests\Unit\Session\Factory;

use Phalcon\Tests\AbstractUnitTestCase;

final class LoadTest extends AbstractUnitTestCase
{
//    use FactoryTrait;

    public function setUp(): void
    {
        $this->markTestSkipped('TODO: CHECKME');

        $this->init();
    }

    /**
     * Tests Phalcon\Session\Factory :: load() - array
     *
     * @author Wojciech Ślawski <jurigag@gmail.com>
     * @since  2017-03-02
     */
    public function testSessionFactoryLoadArray(): void
    {
        $options = $this->arrayConfig['session'];
        $data    = $options;

        $this->runTests($options, $data);
    }

    /**
     * Tests Phalcon\Session\Factory :: load() - Config
     *
     * @author Wojciech Ślawski <jurigag@gmail.com>
     * @since  2017-03-02
     */
    public function testSessionFactoryLoadConfig(): void
    {
        $this->wantToTest("Session\Factory - load() - Config");

        $options = $this->config->session;
        $data    = $options->toArray();

        $this->runTests($options, $data);
    }

    private function runTests($options, array $data)
    {
        $session = Factory::load($options);

        $this->assertInstanceOf(Files::class, $session);

        $expected = $session->getOptions();

        $actual = array_intersect_assoc(
            $session->getOptions(),
            $data
        );

        $this->assertEquals($expected, $actual);
    }
}
