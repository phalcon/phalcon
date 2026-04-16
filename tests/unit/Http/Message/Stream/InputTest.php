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

namespace Phalcon\Tests\Unit\Http\Message\Stream;

use Phalcon\Http\Message\Stream\Input;
use Phalcon\Tests\AbstractUnitTestCase;

final class InputTest extends AbstractUnitTestCase
{
    /**
     * Tests Phalcon\Http\Message\Stream\Input :: isWritable()
     *
     * @return void
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2024-01-01
     */
    public function testHttpMessageStreamInputIsWritable(): void
    {
        $input = new Input();

        $this->assertFalse($input->isWritable());
    }

    /**
     * Tests Phalcon\Http\Message\Stream\Input :: getContents()
     *
     * @return void
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2024-01-01
     */
    public function testHttpMessageStreamInputGetContents(): void
    {
        $input = new Input();

        $contents = $input->getContents();
        $this->assertIsString($contents);
    }

    /**
     * Tests Phalcon\Http\Message\Stream\Input :: getContents() - after eof
     *
     * @return void
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2024-01-01
     */
    public function testHttpMessageStreamInputGetContentsAfterEof(): void
    {
        $input = new Input();

        // First call reads to EOF
        $first = $input->getContents();

        // Second call returns cached data (eof branch)
        $second = $input->getContents();
        $this->assertSame($first, $second);
    }

    /**
     * Tests Phalcon\Http\Message\Stream\Input :: __toString()
     *
     * @return void
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2024-01-01
     */
    public function testHttpMessageStreamInputToString(): void
    {
        $input = new Input();

        $result = (string) $input;
        $this->assertIsString($result);
    }

    /**
     * Tests Phalcon\Http\Message\Stream\Input :: __toString() - after eof
     *
     * @return void
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2024-01-01
     */
    public function testHttpMessageStreamInputToStringAfterEof(): void
    {
        $input = new Input();

        // Trigger eof via getContents
        $input->getContents();

        // __toString should return cached data (eof branch)
        $result = (string) $input;
        $this->assertIsString($result);
    }

    /**
     * Tests Phalcon\Http\Message\Stream\Input :: read()
     *
     * @return void
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2024-01-01
     */
    public function testHttpMessageStreamInputRead(): void
    {
        $input = new Input();

        $data = $input->read(8192);
        $this->assertIsString($data);
    }
}
