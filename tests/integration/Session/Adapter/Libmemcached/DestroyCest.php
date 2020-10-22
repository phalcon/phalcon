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

use function serialize;
use function uniqid;

class DestroyCest
{
    use DiTrait;

    /**
     * Tests Phalcon\Session\Adapter\Libmemcached :: destroy()
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2018-11-13
     */
    public function sessionAdapterLibmemcachedDestroy(IntegrationTester $I)
    {
        $I->wantToTest('Session\Adapter\Libmemcached - destroy()');

        $adapter = $this->newService('sessionLibmemcached');

        $value  = uniqid();
        $key    = 'sess-memc-test1';
        $actual = serialize($value);

        $I->haveInMemcached($key, $actual);

        $I->seeInMemcached(
            'sess-memc-test1',
            serialize($value)
        );

        $actual = $adapter->destroy('test1');
        $I->assertTrue($actual);

        $I->dontSeeInMemcached($key);
    }
}
