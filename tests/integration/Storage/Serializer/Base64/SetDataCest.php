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

namespace Phalcon\Tests\Integration\Storage\Serializer\Base64;

use IntegrationTester;
use Phalcon\Storage\Serializer\Base64;

class SetDataCest
{
    /**
     * Tests Phalcon\Storage\Serializer\Base64 :: getData()
     *
     * @param IntegrationTester $I
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2020-09-09
     */
    public function storageSerializerBase64SetData(IntegrationTester $I)
    {
        $I->wantToTest('Storage\Serializer\Base64 - setData()');

        $data       = ['Phalcon Framework'];
        $serializer = new Base64();

        $I->assertNull(
            $serializer->getData()
        );

        $serializer->setData($data);

        $I->assertEquals(
            $data,
            $serializer->getData()
        );
    }
}
