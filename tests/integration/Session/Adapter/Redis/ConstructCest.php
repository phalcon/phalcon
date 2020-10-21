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
use SessionHandlerInterface;

/**
 * Class ConstructCest
 *
 * @package Phalcon\Tests\Integration\Session\Adapter\Redis
 */
class ConstructCest
{
    use DiTrait;

    /**
     * Tests Phalcon\Session\Adapter\Redis :: __construct()
     *
     * @param IntegrationTester $I
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2020-09-09
     */
    public function sessionAdapterRedisConstruct(IntegrationTester $I)
    {
        $I->wantToTest('Session\Adapter\Redis - __construct()');

        $adapter = $this->newService('sessionRedis');

        $class = SessionHandlerInterface::class;
        $I->assertInstanceOf($class, $adapter);
    }
}
