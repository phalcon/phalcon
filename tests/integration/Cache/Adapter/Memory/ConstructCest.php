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

use IntegrationTester;
use Phalcon\Cache\Adapter\AdapterInterface;
use Phalcon\Cache\Adapter\Memory;
use Phalcon\Storage\SerializerFactory;
use Phalcon\Support\Exception as HelperException;
use Phalcon\Support\HelperFactory;

class ConstructCest
{
    /**
     * Tests Phalcon\Cache\Adapter\Memory :: __construct()
     *
     * @param IntegrationTester $I
     *
     * @throws HelperException
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2020-09-09
     */
    public function storageAdapterMemoryConstruct(IntegrationTester $I)
    {
        $I->wantToTest('Cache\Adapter\Memory - __construct()');

        $helper     = new HelperFactory();
        $serializer = new SerializerFactory();
        $adapter    = new Memory($helper, $serializer);

        $class = Memory::class;
        $I->assertInstanceOf($class, $adapter);

        $class = AdapterInterface::class;
        $I->assertInstanceOf($class, $adapter);
    }
}
