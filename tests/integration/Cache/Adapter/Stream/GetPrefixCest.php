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

namespace Phalcon\Tests\Integration\Cache\Adapter\Stream;

use IntegrationTester;
use Phalcon\Cache\Adapter\Stream;
use Phalcon\Storage\Exception as CacheException;
use Phalcon\Storage\SerializerFactory;
use Phalcon\Support\Exception as HelperException;

use function outputDir;

class GetPrefixCest
{
    /**
     * Tests Phalcon\Cache\Adapter\Stream :: getPrefix()
     *
     * @param IntegrationTester $I
     *
     * @throws HelperException
     * @throws CacheException
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2020-09-09
     */
    public function storageAdapterStreamGetSetPrefix(IntegrationTester $I)
    {
        $I->wantToTest('Cache\Adapter\Stream - getPrefix()');

        $serializer = new SerializerFactory();
        $adapter    = new Stream(
            $serializer,
            [
                'storageDir' => outputDir(),
                'prefix'     => 'my-prefix',
            ]
        );

        $expected = 'my-prefix';
        $actual   = $adapter->getPrefix();
        $I->assertEquals($expected, $actual);
    }

    /**
     * Tests Phalcon\Cache\Adapter\Stream :: getPrefix() - default
     *
     * @param IntegrationTester $I
     *
     * @throws HelperException
     * @throws CacheException
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2020-09-09
     */
    public function storageAdapterStreamGetSetPrefixDefault(IntegrationTester $I)
    {
        $I->wantToTest('Cache\Adapter\Stream - getPrefix() - default');

        $serializer = new SerializerFactory();
        $adapter    = new Stream(
            $serializer,
            [
                'storageDir' => outputDir(),
            ]
        );

        $expected = 'ph-strm';
        $actual   = $adapter->getPrefix();
        $I->assertEquals($expected, $actual);
    }
}
