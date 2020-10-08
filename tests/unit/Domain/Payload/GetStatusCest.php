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
 * Class GetStatusCest
 */
class GetStatusCest
{
    /**
     * Unit Tests Phalcon\Domain\Payload :: getStatus()
     *
     * @param UnitTester $I
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2020-09-09
     */
    public function httpPayloadPayloadGetStatus(UnitTester $I)
    {
        $I->wantToTest('Domain\Payload\Payload - getStatus()');

        $payload = new Payload(DomainStatus::PROCESSING);

        $expected = DomainStatus::PROCESSING;
        $actual   = $payload->getStatus();
        $I->assertEquals($expected, $actual);
    }
}
