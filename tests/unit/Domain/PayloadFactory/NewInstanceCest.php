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

namespace Phalcon\Tests\Unit\Domain\PayloadFactory;

use PayloadInterop\DomainPayload;
use PayloadInterop\DomainStatus;
use Phalcon\Domain\PayloadFactory;
use UnitTester;

/**
 * Class NewInstanceCest
 */
class NewInstanceCest
{
    /**
     * Unit Tests Phalcon\Domain\PayloadFactory :: newInstance()
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2019-09-09
     *
     * @param UnitTester $I
     */
    public function httpPayloadPayloadFactoryNewInstance(UnitTester $I)
    {
        $I->wantToTest('Domain\Payload\PayloadFactory - newInstance()');

        $factory = new PayloadFactory();
        $payload = $factory->newInstance(
            DomainStatus::ACCEPTED,
            [
                'one' => 'two'
            ]
        );

        $I->assertInstanceOf(DomainPayload::class, $payload);
    }
}
