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

namespace Phalcon\Tests\Unit\Domain\Payload;

use PayloadInterop\DomainStatus;
use Phalcon\Domain\Payload;
use UnitTester;

/**
 * Class GetResultCest
 */
class GetResultCest
{
    /**
     * Unit Tests Phalcon\Domain\Payload :: getResult
     *
     * @param UnitTester $I
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2020-09-09
     */
    public function httpPayloadPayloadGetMessages(UnitTester $I)
    {
        $I->wantToTest('Domain\Payload - getResult()');

        $payload = new Payload(
            DomainStatus::ACCEPTED,
            [
                'one' => 'two',
            ]
        );

        $expected = ['one' => 'two'];
        $actual   = $payload->getResult();
        $I->assertEquals($expected, $actual);
    }
}
