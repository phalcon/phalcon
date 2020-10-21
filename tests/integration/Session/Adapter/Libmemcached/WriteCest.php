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

use function uniqid;

/**
 * Class WriteCest
 *
 * @package Phalcon\Tests\Integration\Session\Adapter\Libmemcached
 */
class WriteCest
{
    use DiTrait;

    /**
     * Tests Phalcon\Session\Adapter\Libmemcached :: write()
     *
     * @param IntegrationTester $I
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2020-09-09
     */
    public function sessionAdapterLibmemcachedWrite(IntegrationTester $I)
    {
        $I->wantToTest('Session\Adapter\Libmemcached - write()');

        $adapter = $this->newService('sessionLibmemcached');
        $value   = uniqid();
        $adapter->write('test1', $value);

        /**
         * Serialize the value because the adapter does not have a serializer
         */
        $I->seeInMemcached('sess-memc-test1', $value);
        $I->clearMemcache();
    }
}
