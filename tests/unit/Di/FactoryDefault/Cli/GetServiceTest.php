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

namespace Phalcon\Tests\Unit\Di\FactoryDefault\Cli;

use Phalcon\Di\Exception;
use Phalcon\Di\FactoryDefault\Cli as Di;
use Phalcon\Di\Service;
use Phalcon\Html\Escaper;
use Phalcon\Tests\AbstractUnitTestCase;

final class GetServiceTest extends AbstractUnitTestCase
{
    /**
     * Tests Phalcon\Di\FactoryDefault\Cli :: getService()
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2018-11-13
     */
    public function testDiFactorydefaultCliGetService(): void
    {
        // setup
        $di = new Di();

        // set a service and get it to check
        $actual = $di->set('escaper', Escaper::class);
        $this->assertInstanceOf(Service::class, $actual);

        // get escaper service
        $actual = $di->getService('escaper');

        $this->assertInstanceOf(Service::class, $actual);
        $this->assertFalse($actual->isShared());

        // non exists service
        $this->expectException(Exception::class);
        $this->expectExceptionMessage(
            "Service 'non-exists' was not found in the dependency injection container"
        );

        $di->getService('non-exists');
    }
}
