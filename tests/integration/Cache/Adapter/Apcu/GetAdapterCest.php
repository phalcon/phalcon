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

namespace Phalcon\Tests\Integration\Cache\Adapter\Apcu;

use IntegrationTester;
use Phalcon\Cache\Adapter\Apcu;
use Phalcon\Storage\SerializerFactory;
use Phalcon\Support\Exception;
use Phalcon\Tests\Fixtures\Traits\ApcuTrait;

class GetAdapterCest
{
    use ApcuTrait;

    /**
     * Tests Phalcon\Cache\Adapter\Apcu :: getAdapter()
     *
     * @param IntegrationTester $I
     *
     * @throws Exception
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2020-09-09
     */
    public function storageAdapterApcuGetAdapter(IntegrationTester $I)
    {
        $I->wantToTest('Cache\Adapter\Apcu - getAdapter()');

        $serializer = new SerializerFactory();
        $adapter    = new Apcu($serializer);

        $actual = $adapter->getAdapter();
        $I->assertNull($actual);
    }
}
