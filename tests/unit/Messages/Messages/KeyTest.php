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

namespace Phalcon\Tests\Unit\Messages\Messages;

use Phalcon\Messages\Message;
use Phalcon\Messages\Messages;
use Phalcon\Tests\UnitTestCase;

final class KeyTest extends UnitTestCase
{
    /**
     * Tests Phalcon\Messages\Messages :: key()
     *
     * @return void
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2020-09-09
     */
    public function testMessagesMessagesKey(): void
    {
        $messages = new Messages(
            [
                0 => new Message(
                    'This is a message #1',
                    'MyField1',
                    'MyType1',
                    111
                ),
                1 => new Message(
                    'This is a message #2',
                    'MyField2',
                    'MyType2',
                    222
                ),
            ]
        );

        $messages->next();

        $this->assertSame(
            1,
            $messages->key()
        );
    }
}
