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

namespace Phalcon\Tests\Unit\Forms\Form;

use Phalcon\Filter\Validation;
use Phalcon\Filter\Validation\Validator\PresenceOf;
use Phalcon\Filter\Validation\Validator\Regex;
use Phalcon\Forms\Element\Text;
use Phalcon\Forms\Form;
use Phalcon\Messages\Message;
use Phalcon\Messages\Messages;
use Phalcon\Tests\AbstractUnitTestCase;
use Phalcon\Tests\Support\Forms\ValidationForm;
use Phalcon\Tests\Unit\Forms\Fake\FakeFormAfterValidation;
use Phalcon\Tests\Unit\Forms\Fake\FakeFormBeforeValidation;
use stdClass;

final class IsValidTest extends AbstractUnitTestCase
{
    /**
     * @issue  https://github.com/phalcon/cphalcon/issues/13149
     * @author Phalcon Team <team@phalcon.io>
     * @since  2018-11-13
     */
    public function testFormsFormRemoveIsValidCancelOnFail(): void
    {
        $form = new ValidationForm();

        $data = [
            'fullname' => '',
            'email'    => '',
            'subject'  => '',
            'message'  => '',
        ];

        $actual = $form->isValid($data);
        $this->assertFalse($actual);

        /**
         * 6 validators in total
         */
        $messages = $form->getMessages();
        $this->assertCount(4, $messages);

        $data = [
            'fullname' => '',
            'email'    => 'team@phalcon.io',
            'subject'  => 'Some subject',
            'message'  => 'Some message',
        ];

        $actual = $form->isValid($data);
        $this->assertFalse($actual);

        $messages = $form->getMessages();
        $this->assertCount(1, $messages);

        $expected = new Messages(
            [
                new Message(
                    'your fullname is required',
                    'fullname',
                    PresenceOf::class
                ),
            ]
        );

        $this->assertEquals($expected, $messages);
    }

    /**
     * @issue  https://github.com/phalcon/cphalcon/issues/11500
     * @author Mohamad Rostami <rostami@outlook.com>
     */
    public function testMergeValidators(): void
    {
        $telephone = new Text('telephone');
        $telephone->addValidators(
            [
                new PresenceOf(
                    [
                        'message' => 'The telephone is required',
                    ]
                ),
            ]
        );

        $customValidation = new Validation();
        $customValidation->add(
            'telephone',
            new Regex(
                [
                    'pattern' => '/\+44 [0-9]+ [0-9]+/',
                    'message' => 'The telephone has an invalid format',
                ]
            )
        );

        $form = new Form();

        $address = new Text('address');

        $form->add($telephone);
        $form->add($address);

        $form->setValidation($customValidation);

        $this->assertFalse(
            $form->isValid(
                [
                    'address' => 'hello',
                ]
            )
        );

        $this->assertTrue(
            $form->get('telephone')->hasMessages()
        );

        $this->assertFalse(
            $form->get('address')->hasMessages()
        );


        $expected = new Messages(
            [
                new Message(
                    'The telephone has an invalid format',
                    'telephone',
                    Regex::class,
                    0
                ),
                new Message(
                    'The telephone is required',
                    'telephone',
                    PresenceOf::class,
                    0
                ),
            ]
        );

        $this->assertEquals(
            $expected,
            $form->get('telephone')->getMessages()
        );


        $expected = $form->getMessages();

        $this->assertEquals(
            $expected,
            $form->get('telephone')->getMessages()
        );


        $expected = new Messages();

        $this->assertEquals(
            $expected,
            $form->get('address')->getMessages()
        );
    }

    /**
     * @author Phalcon Team <team@phalcon.io>
     * @since  2024-01-01
     */
    public function testFormsFormIsValidNoElementsReturnsTrue(): void
    {
        $form   = new Form();
        $actual = $form->isValid([]);
        $this->assertTrue($actual);
    }

    /**
     * @author Phalcon Team <team@phalcon.io>
     * @since  2024-01-01
     */
    public function testFormsFormIsValidBeforeValidationReturnsFalse(): void
    {
        $form = new FakeFormBeforeValidation();
        $form->add(new Text('name'));

        $actual = $form->isValid(['name' => 'test']);
        $this->assertFalse($actual);
    }

    /**
     * @author Phalcon Team <team@phalcon.io>
     * @since  2024-01-01
     */
    public function testFormsFormIsValidAfterValidationCalled(): void
    {
        $form = new FakeFormAfterValidation();
        $form->add(new Text('name'));

        $form->isValid(['name' => 'test']);

        $this->assertTrue($form->afterCalled);
    }
}
