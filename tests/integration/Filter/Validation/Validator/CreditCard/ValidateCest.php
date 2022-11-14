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

namespace Phalcon\Tests\Integration\Filter\Validation\Validator\CreditCard;

use IntegrationTester;
use Phalcon\Filter\Validation;
use Phalcon\Filter\Validation\Validator\CreditCard;
use Phalcon\Messages\Message;
use Phalcon\Messages\Messages;

/**
 * Class ValidateCest
 */
class ValidateCest
{
    /**
     * Tests Phalcon\Filter\Validation\Validator\CreditCard :: validate() - single
     * field
     *
     * @author Wojciech Ślawski <jurigag@gmail.com>
     * @since  2016-06-05
     */
    public function filterValidationValidatorCreditCardValidateSingleField(IntegrationTester $I)
    {
        $I->wantToTest("Validation\Validator\CreditCard - validate() - single");

        $validation = new Validation();

        $validation->add(
            'creditCard',
            new CreditCard()
        );

        $I->assertCount(
            0,
            $validation->validate(
                [
                    'creditCard' => 4601587377626131,
                ]
            )
        );

        $I->assertCount(
            1,
            $validation->validate(
                [
                    'creditCard' => 46015873776261312,
                ]
            )
        );
    }

    /**
     * Tests Phalcon\Filter\Validation\Validator\CreditCard :: validate() - multiple
     * field
     *
     * @author Wojciech Ślawski <jurigag@gmail.com>
     * @since  2016-06-05
     */
    public function filterValidationValidatorCreditCardValidateMultipleFields(IntegrationTester $I)
    {
        $I->wantToTest("Validation\Validator\CreditCard - validate() - multiple");

        $validation = new Validation();

        $validationMessages = [
            'creditCard'        => 'CreditCard must be correct credit card value.',
            'anotherCreditCard' => 'AnotherCreditCard must be correct credit card value.',
        ];

        $validation->add(
            ['creditCard', 'anotherCreditCard'],
            new CreditCard(
                [
                    'message' => $validationMessages,
                ]
            )
        );


        $messages = $validation->validate(
            [
                'creditCard'        => 4601587377626131,
                'anotherCreditCard' => 4601587377626131,
            ]
        );

        $I->assertEquals(
            0,
            $messages->count()
        );


        $messages = $validation->validate(
            [
                'creditCard'        => 46015873776261312,
                'anotherCreditCard' => 4601587377626131,
            ]
        );

        $I->assertEquals(
            1,
            $messages->count()
        );


        $I->assertEquals(
            $validationMessages['creditCard'],
            $messages->offsetGet(0)->getMessage()
        );


        $messages = $validation->validate(
            [
                'creditCard'        => 46015873776261312,
                'anotherCreditCard' => 46015873776261312,
            ]
        );

        $I->assertEquals(
            2,
            $messages->count()
        );


        $I->assertEquals(
            $validationMessages['creditCard'],
            $messages->offsetGet(0)->getMessage()
        );


        $I->assertEquals(
            $validationMessages['anotherCreditCard'],
            $messages->offsetGet(1)->getMessage()
        );
    }

    /**
     * Tests Phalcon\Filter\Validation\Validator\CreditCard :: validate() - valid card
     * numbers
     *
     * @author Caio Almeida <caio.f.r.amd@gmail.com>
     * @since  2015-09-06
     */
    public function filterValidationValidatorCreditCardValidateValidCreditCard(IntegrationTester $I)
    {
        $I->wantToTest("Validation\Validator\CreditCard - validate() - valid card");

        $providers = [
            'amex'       => '378282246310005',
            'visa'       => '4012888888881881',
            'dinners'    => '38520000023237',
            'mastercard' => '5105105105105100',
            'discover'   => '6011000990139424',
        ];

        foreach ($providers as $number) {
            $validation = new Validation();

            $validation->add(
                'creditCard',
                new CreditCard()
            );

            $I->assertCount(
                0,
                $validation->validate(
                    [
                        'creditCard' => $number,
                    ]
                )
            );
        }
    }

    /**
     * Tests Phalcon\Filter\Validation\Validator\CreditCard :: validate() - invalid
     * card numbers
     *
     * @author Caio Almeida <caio.f.r.amd@gmail.com>
     * @since  2015-09-06
     */
    public function filterValidationValidatorCreditCardValidateInvalidCreditCard(IntegrationTester $I)
    {
        $I->wantToTest("Validation\Validator\CreditCard - validate() - invalid card");

        $cards = [
            '1203191201121221',
            '102030102320',
            '120120s201023',
            '20323200003230',
            '12010012',
        ];

        foreach ($cards as $number) {
            $validation = new Validation();

            $validation->add(
                'creditCard',
                new CreditCard()
            );


            $expected = new Messages(
                [
                    new Message(
                        'Field creditCard is not valid for a credit card number',
                        'creditCard',
                        CreditCard::class,
                        0
                    ),
                ]
            );

            $actual = $validation->validate(
                [
                    'creditCard' => $number,
                ]
            );

            $I->assertEquals($expected, $actual);
        }
    }
}
