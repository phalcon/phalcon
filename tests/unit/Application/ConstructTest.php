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

namespace Phalcon\Tests\Unit\Application;

use Phalcon\Application\AbstractApplication;
use Phalcon\Di\InjectionAwareInterface;
use Phalcon\Events\EventsAwareInterface;
use Phalcon\Tests\Fixtures\Application\ApplicationFixture;
use Phalcon\Tests\UnitTestCase;

final class ConstructTest extends UnitTestCase
{
    /**
     * Tests Phalcon\Application\* :: __construct()
     *
     * @return void
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2020-09-09
     */
    public function testApplicationConstruct(): void
    {
        $application = new ApplicationFixture();

        $this->assertInstanceOf(EventsAwareInterface::class, $application);
        $this->assertInstanceOf(InjectionAwareInterface::class, $application);
        $this->assertInstanceOf(AbstractApplication::class, $application);
    }
}
