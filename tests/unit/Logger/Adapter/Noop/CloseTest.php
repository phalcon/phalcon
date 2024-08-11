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

namespace Phalcon\Tests\Unit\Logger\Adapter\Noop;

use Phalcon\Logger\Adapter\Noop;
use Phalcon\Tests\AbstractUnitTestCase;

final class CloseTest extends AbstractUnitTestCase
{
    /**
     * Tests Phalcon\Logger\Adapter\Noop :: close()
     *
     * @return void
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2020-09-09
     */
    public function testLoggerAdapterNoopClose(): void
    {
        $adapter = new Noop();

        $this->assertTrue(
            $adapter->close()
        );
    }
}
