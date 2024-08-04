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
use Phalcon\Logger\Exception;
use Phalcon\Tests\UnitTestCase;

use function dataDir;
use function file_get_contents;
use function serialize;

final class SerializeUnserializeTest extends UnitTestCase
{
    /**
     * Tests Phalcon\Logger\Adapter\Noop :: serialize()/unserialize
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2021-09-03
     * @issue  15638
     */
    public function testLoggerAdapterNoopSerializeUnserializeException(): void
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage("This object cannot be serialized");

        $adapter = new Noop();
        $object  = serialize($adapter);
    }

    /**
     * Tests Phalcon\Logger\Adapter\Noop :: serialize()/unserialize
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2021-09-03
     * @issue  15638
     */
    public function testLoggerAdapterNoopSerializeUnserialize(): void
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage("This object cannot be unserialized");

        $serialized = file_get_contents(dataDir('assets/logger/logger.serialized'));
        $object     = unserialize($serialized);
    }
}
