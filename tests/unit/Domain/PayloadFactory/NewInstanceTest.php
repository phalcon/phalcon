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
use Phalcon\Tests\AbstractUnitTestCase;

final class NewInstanceTest extends AbstractUnitTestCase
{
    /**
     * Unit Tests Phalcon\Domain\PayloadFactory :: newInstance()
     *
     * @return void
     *
     * @since  2019-09-09
     *
     * @author Phalcon Team <team@phalcon.io>
     */
    public function testDomainPayloadPayloadFactoryNewInstance(): void
    {
        $factory = new PayloadFactory();
        $payload = $factory->newInstance(
            DomainStatus::ACCEPTED,
            [
                'one' => 'two',
            ]
        );

        $this->assertInstanceOf(DomainPayload::class, $payload);
    }
}
