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

namespace Phalcon\Tests\Integration\Session\Adapter\Redis;

use IntegrationTester;
use Phalcon\Tests\Fixtures\Traits\DiTrait;

use function uniqid;

/**
 * Class ReadCest
 *
 * @package Phalcon\Tests\Integration\Session\Adapter\Redis
 */
class ReadCest
{
    use DiTrait;

    /**
     * Tests Phalcon\Session\Adapter\Redis :: read()
     *
     * @param IntegrationTester $I
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2020-09-09
     */
    public function sessionAdapterRedisRead(IntegrationTester $I)
    {
        $I->wantToTest('Session\Adapter\Redis - read()');

        $adapter = $this->newService('sessionRedis');
        $value   = uniqid();

        $I->haveInRedis('string', 'sess-reds-test1', $value);

        $expected = $value;
        $actual   = $adapter->read('test1');
        $I->assertEquals($expected, $actual);
        $I->sendCommandToRedis('del', 'sess-reds-test1');

        $actual = $adapter->read('test1');
        $I->assertNotNull($actual);
    }
}
