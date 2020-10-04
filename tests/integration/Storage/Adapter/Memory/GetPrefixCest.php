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

namespace Phalcon\Tests\Integration\Storage\Adapter\Memory;

use Phalcon\Support\Exception as HelperException;
use Phalcon\Storage\Adapter\Memory;
use Phalcon\Storage\SerializerFactory;
use Phalcon\Support\HelperFactory;
use UnitTester;

class GetPrefixCest
{
    /**
     * Tests Phalcon\Storage\Adapter\Memory :: getPrefix()
     *
     * @param UnitTester $I
     *
     * @throws HelperException
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2020-09-09
     */
    public function storageAdapterMemoryGetSetPrefix(UnitTester $I)
    {
        $I->wantToTest('Storage\Adapter\Memory - getPrefix()');

        $helper     = new HelperFactory();
        $serializer = new SerializerFactory();
        $adapter    = new Memory(
            $helper,
            $serializer,
            [
                'prefix' => 'my-prefix',
            ]
        );

        $expected = 'my-prefix';
        $actual   = $adapter->getPrefix();
        $I->assertEquals($expected, $actual);
    }

    /**
     * Tests Phalcon\Storage\Adapter\Memory :: getPrefix() - default
     *
     * @param UnitTester $I
     *
     * @throws HelperException
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2020-09-09
     */
    public function storageAdapterMemoryGetSetPrefixDefault(UnitTester $I)
    {
        $I->wantToTest('Storage\Adapter\Memory - getPrefix() - default');

        $helper     = new HelperFactory();
        $serializer = new SerializerFactory();
        $adapter    = new Memory($helper, $serializer);

        $expected = 'ph-memo-';
        $actual   = $adapter->getPrefix();
        $I->assertEquals($expected, $actual);
    }
}
