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
use TypeError;
use UnitTester;

use function substr;

class ConstructCest
{
    /**
     * Tests Phalcon\Messages\Message :: __construct()
     *
     * @param UnitTester $I
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2020-09-09
     */
    public function messagesMessageConstruct(UnitTester $I): void
    {
        $I->wantToTest('Messages\Message - __construct()');

        $message = new Message(
            'This is a message #1',
            'MyField',
            'MyType',
            111
        );

        $I->assertEquals(
            'This is a message #1',
            $message->getMessage()
        );

        $I->assertEquals(
            'MyField',
            $message->getField()
        );

        $I->assertEquals(
            'MyType',
            $message->getType()
        );

        $I->assertEquals(
            111,
            $message->getCode()
        );
    }

    /**
     * Tests Phalcon\Messages\Message :: __construct() - chain
     *
     * @param UnitTester $I
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2020-09-09
     */
    public function messagesMessageConstructChain(UnitTester $I): void
    {
        $I->wantToTest('Messages\Message - __construct() - chain');

        $message = new Message('This is a message #1');

        $message
            ->setField('MyField')
            ->setType('MyType')
            ->setCode(111)
        ;

        $I->assertEquals(
            'This is a message #1',
            $message->getMessage()
        );

        $I->assertEquals(
            'MyField',
            $message->getField()
        );

        $I->assertEquals(
            'MyType',
            $message->getType()
        );

        $I->assertEquals(
            111,
            $message->getCode()
        );
    }

    /**
     * Tests Phalcon\Messages\Message :: __construct() - exception
     *
     * @param UnitTester $I
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2020-09-09
     */
    public function messagesMessageConstructException(UnitTester $I): void
    {
        $I->wantToTest('Messages\Message - __construct() - exception');

        /**
         * Sometimes Travis reports 'boolean' vs 'bool' and the test fails. This
         * is why `expectThrowable` is not used here
         */
        $actual = '';

        try {
            /** @noinspection PhpStrictTypeCheckingInspection */
            (new Message(true));
        } catch (TypeError $ex) {
            $actual = $ex->getMessage();
        }

        $I->assertEquals(
            'Argument 1 passed to Phalcon\Messages\Message::__construct() must be of the type string, bool',
            substr($actual, 0, 93)
        );
    }
}
