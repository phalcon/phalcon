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

class GetAdapterCest
{
    use ApcuTrait;

    /**
     * Tests Phalcon\Storage\Adapter\Apcu :: getAdapter()
     *
     * @param UnitTester $I
     *
     * @throws Exception
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2020-09-09
     */
    public function storageAdapterApcuGetAdapter(UnitTester $I)
    {
        $I->wantToTest('Storage\Adapter\Apcu - getAdapter()');

        $serializer = new SerializerFactory();
        $adapter    = new Apcu($serializer);

        $actual = $adapter->getAdapter();
        $I->assertNull($actual);
    }
}
