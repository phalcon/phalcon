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

namespace Phalcon\Tests\Integration\Cache\Adapter\Memory;

use Phalcon\Support\Exception as HelperException;
use Phalcon\Cache\Adapter\Memory;
use Phalcon\Storage\SerializerFactory;
use Phalcon\Support\HelperFactory;
use IntegrationTester;

class GetAdapterCest
{
    /**
     * Tests Phalcon\Cache\Adapter\Memory :: getAdapter()
     *
     * @param IntegrationTester $I
     *
     * @throws HelperException
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2020-09-09
     */
    public function storageAdapterMemoryGetAdapter(IntegrationTester $I)
    {
        $I->wantToTest('Cache\Adapter\Memory - getAdapter()');

        $helper     = new HelperFactory();
        $serializer = new SerializerFactory();
        $adapter    = new Memory($helper, $serializer);

        $actual = $adapter->getAdapter();
        $I->assertNull($actual);
    }
}
