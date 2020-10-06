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

namespace Phalcon\Tests\Integration\Session\Adapter\Libmemcached;

use IntegrationTester;
use Phalcon\Tests\Fixtures\Traits\DiTrait;
use Phalcon\Tests\Fixtures\Traits\SessionTrait;
use SessionHandlerInterface;

class ConstructCest
{
    use DiTrait;

    /**
     * Tests Phalcon\Session\Adapter\Libmemcached :: __construct()
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2018-11-13
     */
    public function sessionAdapterLibmemcachedConstruct(IntegrationTester $I)
    {
        $I->wantToTest('Session\Adapter\Libmemcached - __construct()');

        $adapter = $this->newService('sessionLibmemcached');

        $I->assertInstanceOf(
            SessionHandlerInterface::class,
            $adapter
        );
    }
}
