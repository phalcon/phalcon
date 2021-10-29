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

namespace Phalcon\Tests\Integration\Storage\Adapter\Stream;

use IntegrationTester;
use Phalcon\Storage\Adapter\Stream;
use Phalcon\Storage\Exception as StorageException;
use Phalcon\Storage\SerializerFactory;
use Phalcon\Support\Exception as HelperException;

class GetAdapterCest
{
    /**
     * Tests Phalcon\Storage\Adapter\Stream :: getAdapter()
     *
     * @param IntegrationTester $I
     *
     * @throws HelperException
     * @throws StorageException
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2020-09-09
     */
    public function storageAdapterStreamGetAdapter(IntegrationTester $I)
    {
        $I->wantToTest('Storage\Adapter\Stream - getAdapter()');

        $serializer = new SerializerFactory();
        $adapter    = new Stream(
            $serializer,
            [
                'storageDir' => '/tmp',
            ]
        );

        $actual = $adapter->getAdapter();
        $I->assertNull($actual);
    }
}
