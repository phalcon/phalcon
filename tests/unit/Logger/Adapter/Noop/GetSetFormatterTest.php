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
use Phalcon\Logger\Formatter\FormatterInterface;
use Phalcon\Logger\Formatter\Line;
use Phalcon\Tests\UnitTestCase;

final class GetSetFormatterTest extends UnitTestCase
{
    /**
     * Tests Phalcon\Logger\Adapter\Noop :: getFormatter()/setFormatter()
     *
     * @return void
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2020-09-09
     */
    public function testLoggerAdapterNoopGetSetFormatter(): void
    {
        $adapter = new Noop();

        $adapter->setFormatter(new Line());

        $this->assertInstanceOf(FormatterInterface::class, $adapter->getFormatter());
    }
}
