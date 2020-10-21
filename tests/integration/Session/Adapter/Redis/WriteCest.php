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
 * Class WriteCest
 *
 * @package Phalcon\Tests\Integration\Session\Adapter\Redis
 */
class WriteCest
{
    use DiTrait;

    /**
     * Tests Phalcon\Session\Adapter\Redis :: write()
     *
     * @param IntegrationTester $I
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2020-09-09
     */
    public function sessionAdapterRedisWrite(IntegrationTester $I)
    {
        $I->wantToTest('Session\Adapter\Redis - write()');

        $adapter = $this->newService('sessionRedis');
        $value   = uniqid();
        $adapter->write('test1', $value);

        /**
         * Serialize the value because the adapter does not have a serializer
         */
        $expected = serialize($value);
        $actual   = $I->grabFromRedis('sess-reds-test1');
        $I->assertEquals($expected, $actual);
        $I->sendCommandToRedis('del', 'sess-reds-test1');
    }
}
