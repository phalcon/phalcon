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

namespace Phalcon\Tests\Unit\Messages\Message;

use Phalcon\Messages\Message;
use Phalcon\Tests\UnitTestCase;

final class GetSetTypeTest extends UnitTestCase
{
    /**
     * Tests Phalcon\Messages\Message :: getType()/setType()
     *
     * @return void
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2020-09-09
     */
    public function testMessagesMessageGetSetType(): void
    {
        $message = new Message(
            'This is a message #1',
            'MyField',
            'MyType',
            111
        );

        $this->assertSame(
            'MyType',
            $message->getType()
        );
    }
}
