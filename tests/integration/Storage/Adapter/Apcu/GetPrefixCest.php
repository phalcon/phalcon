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

namespace Phalcon\Tests\Integration\Storage\Adapter\Apcu;

use Phalcon\Helper\Exception;
use Phalcon\Storage\Adapter\Apcu;
use Phalcon\Storage\SerializerFactory;
use Phalcon\Tests\Fixtures\Traits\ApcuTrait;
use UnitTester;

class GetPrefixCest
{
    use ApcuTrait;

    /**
     * Tests Phalcon\Storage\Adapter\Apcu :: getPrefix()
     *
     * @param UnitTester $I
     *
     * @throws Exception
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2020-09-09
     */
    public function storageAdapterApcuGetSetPrefix(UnitTester $I)
    {
        $I->wantToTest('Storage\Adapter\Apcu - getPrefix()');

        $serializer = new SerializerFactory();

        $adapter = new Apcu(
            $serializer,
            [
                'prefix' => 'my-prefix',
            ]
        );

        $I->assertEquals(
            'my-prefix',
            $adapter->getPrefix()
        );
    }

    /**
     * Tests Phalcon\Storage\Adapter\Apcu :: getPrefix() - default
     *
     * @param UnitTester $I
     *
     * @throws Exception
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2020-09-09
     */
    public function storageAdapterApcuGetSetPrefixDefault(UnitTester $I)
    {
        $I->wantToTest('Storage\Adapter\Apcu - getPrefix() - default');

        $serializer = new SerializerFactory();
        $adapter    = new Apcu($serializer);

        $I->assertEquals(
            'ph-apcu-',
            $adapter->getPrefix()
        );
    }
}
