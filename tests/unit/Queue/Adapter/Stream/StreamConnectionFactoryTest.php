<?php

/**
 * This file is part of the Phalcon Framework.
 *
 * (c) Phalcon Team <team@phalcon.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Phalcon\Tests\Unit\Queue\Adapter\Stream;

use Phalcon\Queue\Adapter\Stream\StreamConnectionFactory;
use Phalcon\Queue\Adapter\Stream\StreamContext;
use Phalcon\Talon\PHPUnit\AbstractUnitTestCase;

final class StreamConnectionFactoryTest extends AbstractUnitTestCase
{
    /**
     * @author Phalcon Team <team@phalcon.io>
     * @since  2026-11-19
     *
     * @issue  https://github.com/phalcon/phalcon/issues/883
     */
    public function testQueueAdapterStreamStreamConnectionFactoryCreateContextStorageDirInt(): void
    {
        $factory = new StreamConnectionFactory(['storageDir' => 123]);

        $this->assertInstanceOf(StreamContext::class, $factory->createContext());
    }
}
