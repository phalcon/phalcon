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

namespace Phalcon\Tests\Integration\Forms\Element;

use Codeception\Example;
use IntegrationTester;
use Phalcon\Messages\Message;
use Phalcon\Messages\Messages;
use Phalcon\Tests\Fixtures\Traits\FormsTrait;

use function uniqid;

class GetSetHasAppendMessagesCest
{
    use FormsTrait;

    /**
     * Tests Phalcon\Forms\Element\* :: getMessages()/setMessages()/hasMessages()/appendMessage()
     *
     * @dataProvider getExamples
     *
     * @param IntegrationTester $I
     * @param Example           $example
     *
     * @author       Phalcon Team <team@phalcon.io>
     * @since        2021-12-05
     */
    public function formsElementGetSetAppendMessages(IntegrationTester $I, Example $example)
    {
        $I->wantToTest('Forms\Element\* - getMessages()/setMessages()/appendMessage() - ' . $example[0]);

        $name     = uniqid();
        $one      = new Message('one', 'two');
        $two      = new Message('three', 'four');
        $three    = new Message('five', 'six');
        $messages = new Messages(
            [
                $one,
                $two,
            ]
        );

        $class  = $example[1];
        $object = new $class($name);

        $class  = Messages::class;
        $actual = $object->getMessages();
        $I->assertInstanceOf($class, $actual);

        $actual = $object->hasMessages();
        $I->assertFalse($actual);

        $object->setMessages($messages);

        $expected = $messages;
        $actual   = $object->getMessages();
        $I->assertSame($expected, $actual);

        $object->appendMessage($three);

        $messages[] = $three;
        $expected   = $messages;
        $actual     = $object->getMessages();
        $I->assertSame($expected, $actual);

        $actual = $object->hasMessages();
        $I->assertTrue($actual);
    }
}
