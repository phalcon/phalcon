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

namespace Phalcon\Tests\Unit\Di;

use Phalcon\Container\Container;
use Phalcon\Di\Di;
use Phalcon\Tests\AbstractUnitTestCase;
use stdClass;

final class SetDefaultWithContainerTest extends AbstractUnitTestCase
{
    private ?object $defaultContainer = null;

    protected function setUp(): void
    {
        $this->defaultContainer = Di::getDefault();
        Di::reset();
    }

    protected function tearDown(): void
    {
        if (null !== $this->defaultContainer) {
            Di::setDefault($this->defaultContainer);
        }
    }

    /**
     * @author Phalcon Team <team@phalcon.io>
     * @since  2026-04-18
     */
    public function testDiSetDefaultAcceptsContainer(): void
    {
        $container = new Container();
        Di::setDefault($container);

        $this->assertSame($container, Di::getDefault());
    }

    /**
     * @author Phalcon Team <team@phalcon.io>
     * @since  2026-04-18
     */
    public function testDiSetDefaultAcceptsAnyObject(): void
    {
        $obj = new stdClass();
        Di::setDefault($obj);

        $this->assertSame($obj, Di::getDefault());
    }

    /**
     * @author Phalcon Team <team@phalcon.io>
     * @since  2026-04-18
     */
    public function testDiSetDefaultStillAcceptsDi(): void
    {
        $di = new Di();
        Di::setDefault($di);

        $this->assertSame($di, Di::getDefault());
    }
}
