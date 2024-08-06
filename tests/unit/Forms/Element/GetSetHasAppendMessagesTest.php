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

namespace Phalcon\Tests\Unit\Forms\Element;

use Codeception\Example;
use Phalcon\Messages\Message;
use Phalcon\Messages\Messages;
use Phalcon\Tests\Fixtures\Traits\FormsTrait;
use Phalcon\Tests\UnitTestCase;

use function uniqid;

final class GetSetHasAppendMessagesTest extends UnitTestCase
{
    use FormsTrait;

    /**
     * Tests Phalcon\Forms\Element\* :: getMessages()/setMessages()/hasMessages()/appendMessage()
     *
     * @dataProvider getExamples
     *
     * @param Example $example
     *
     * @return void
     * @author       Phalcon Team <team@phalcon.io>
     * @since        2021-12-05
     */
    public function testFormsElementGetSetAppendMessages(
        string $class
    ): void {
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

        $object = new $class($name);

        $className = Messages::class;
        $actual    = $object->getMessages();
        $this->assertInstanceOf($className, $actual);

        $actual = $object->hasMessages();
        $this->assertFalse($actual);

        $object->setMessages($messages);

        $expected = $messages;
        $actual   = $object->getMessages();
        $this->assertSame($expected, $actual);

        $object->appendMessage($three);

        $messages[] = $three;
        $expected   = $messages;
        $actual     = $object->getMessages();
        $this->assertSame($expected, $actual);

        $actual = $object->hasMessages();
        $this->assertTrue($actual);
    }
}
