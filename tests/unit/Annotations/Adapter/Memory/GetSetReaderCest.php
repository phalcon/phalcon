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

namespace Phalcon\Tests\Unit\Annotations\Adapter\Memory;

use Phalcon\Annotations\Adapter\Memory;
use Phalcon\Annotations\Reader;
use UnitTester;

class GetSetReaderCest
{
    /**
     * Tests Phalcon\Annotations\Adapter\Memory :: getReader() / setReader()
     *
     * @author Jeremy PASTOURET <https://github.com/jenovateurs>
     * @since  2020-01-22
     */
    public function annotationsAdapterMemoryGetSetReader(UnitTester $I)
    {
        $I->wantToTest('Annotations\Adapter\Memory - getReader() / setReader()');

        $adapter = new Memory();
        $reader  = new Reader();
        $adapter->setReader($reader);

        $expected = $reader;
        $actual   = $adapter->getReader();
        $I->assertSame($expected, $actual);

        $expected = Reader::class;
        $actual   = $adapter->getReader();
        $I->assertInstanceOf($expected, $actual);
    }
}
