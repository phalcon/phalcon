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
use TypeError;
use UnitTester;

use function substr;

class AppendMessageCest
{
    /**
     * Tests Phalcon\Messages\Messages :: appendMessage()
     *
     * @param UnitTester $I
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2020-09-09
     */
    public function messagesMessagesAppendMessage(UnitTester $I): void
    {
        $I->wantToTest('Messages\Messages - appendMessage()');

        $messages = new Messages();

        $I->assertCount(0, $messages);

        $messages->appendMessage(
            new Message('error a', 'myField', 'MyValidator')
        );

        $I->assertCount(1, $messages);
    }

    /**
     * Tests Phalcon\Messages\Messages :: appendMessage() - exception
     *
     * @param UnitTester $I
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2020-09-09
     */
    public function messagesMessagesAppendMessageException(UnitTester $I): void
    {
        $I->wantToTest('Messages\Messages - appendMessage() - exception');

        /**
         * Sometimes Travis reports 'boolean' vs 'bool' and the test fails. This
         * is why `expectThrowable` is not used here
         */
        $actual = '';

        try {
            /** @noinspection PhpParamsInspection */
            (new Messages())->appendMessage(true);
        } catch (TypeError $ex) {
            $actual = $ex->getMessage();
        }

        $I->assertEquals(
            'Argument 1 passed to Phalcon\Messages\Messages::appendMessage()' .
            ' must implement interface Phalcon\Messages\MessageInterface, bool',
            substr($actual, 0, 128)
        );
    }
}
