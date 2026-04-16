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
use Phalcon\Tests\AbstractUnitTestCase;

final class GetStatusTest extends AbstractUnitTestCase
{
    /**
     * Unit Tests Phalcon\Domain\Payload :: getStatus()
     *
     * @return void
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2020-09-09
     */
    public function testDomainPayloadPayloadGetStatus(): void
    {
        $payload = new Payload(DomainStatus::PROCESSING);

        $expected = DomainStatus::PROCESSING;
        $actual   = $payload->getStatus();
        $this->assertEquals($expected, $actual);
    }
}
