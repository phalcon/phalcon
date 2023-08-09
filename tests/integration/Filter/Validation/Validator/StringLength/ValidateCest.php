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

namespace Phalcon\Tests\Integration\Filter\Validation\Validator\StringLength;

use IntegrationTester;
use Phalcon\Filter\Validation;
use Phalcon\Filter\Validation\Validator\StringLength;
use Phalcon\Filter\Validation\Validator\StringLength\Max;
use Phalcon\Filter\Validation\Validator\StringLength\Min;
use Phalcon\Messages\Message;
use Phalcon\Messages\Messages;

/**
 * Class ValidateCest
 */
class ValidateCest
{
    /**
     * Tests Phalcon\Filter\Validation\Validator\StringLength :: validate() - single
     * field
     *
     * @author Wojciech Ślawski <jurigag@gmail.com>
     * @since  2016-06-05
     */
    public function filterValidationValidatorStringLengthValidateSingleField(IntegrationTester $I)
    {
        $I->wantToTest('Validation\Validator\StringLength :: validate() - single field');

        $validation = new Validation();

        $validation->add(
            'name',
            new StringLength(
                [
                    'min' => 3,
                    'max' => 9,
                ]
            )
        );


        $messages = $validation->validate(
            [
                'name' => 'SomeValue',
            ]
        );

        $I->assertSame(
            0,
            $messages->count()
        );


        $messages = $validation->validate(
            [
                'name' => 'SomeValue123',
            ]
        );

        $I->assertSame(
            1,
            $messages->count()
        );
    }

    /**
     * Tests Phalcon\Filter\Validation\Validator\StringLength :: validate() - minimum
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2013-03-09
     */
    public function filterValidationValidatorStringLengthValidateMinimum(IntegrationTester $I)
    {
        $I->wantToTest('Validation\Validator\StringLength :: validate() - minimum');

        $validation = new Validation();

        $validation->add(
            'name',
            new StringLength(
                [
                    'min' => 3,
                ]
            )
        );


        $messages = $validation->validate(
            [
                'name' => 'Something',
            ]
        );

        $I->assertSame(
            0,
            $messages->count()
        );


        $expected = new Messages(
            [
                new Message(
                    'Field name must be at least 3 characters long',
                    'name',
                    Min::class,
                    0
                ),
            ]
        );

        $messages = $validation->validate(
            [
                'name' => 'So',
            ]
        );

        $I->assertEquals($expected, $messages);
    }

    /**
     * Tests Phalcon\Filter\Validation\Validator\StringLength :: validate() - minimum
     * custom message
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2013-03-09
     */
    public function filterValidationValidatorStringLengthValidateMinimumWithCustomMessage(IntegrationTester $I)
    {
        $I->wantToTest(
            'Validation\Validator\StringLength :: validate() - minimum custom message'
        );

        $validation = new Validation();

        $validation->add(
            'message',
            new StringLength(
                [
                    'min'            => 3,
                    'messageMinimum' => 'The message is too short',
                ]
            )
        );


        $messages = $validation->validate(
            [
                'message' => 'Something',
            ]
        );

        $I->assertSame(
            0,
            $messages->count()
        );


        $expected = new Messages(
            [
                new Message(
                    'The message is too short',
                    'message',
                    Min::class,
                    0
                ),
            ]
        );

        $messages = $validation->validate(
            [
                'message' => 'So',
            ]
        );

        $I->assertEquals($expected, $messages);
    }

    /**
     * Tests Phalcon\Filter\Validation\Validator\StringLength :: validate() - maximum
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2013-03-09
     */
    public function filterValidationValidatorStringLengthValidateMaximum(IntegrationTester $I)
    {
        $I->wantToTest('Validation\Validator\StringLength :: validate() - maximum');

        $validation = new Validation();

        $validation->add(
            'name',
            new StringLength(
                [
                    'max' => 4,
                ]
            )
        );


        $messages = $validation->validate(
            [
                'name' => 'John',
            ]
        );

        $I->assertSame(
            0,
            $messages->count()
        );


        $expected = new Messages(
            [
                new Message(
                    'Field name must not exceed 4 characters long',
                    'name',
                    Max::class,
                    0
                ),
            ]
        );

        $messages = $validation->validate(
            [
                'name' => 'Johannes',
            ]
        );

        $I->assertEquals($expected, $messages);
    }

    /**
     * Tests Phalcon\Filter\Validation\Validator\StringLength :: validate() - maximum
     * custom message
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2013-03-09
     */
    public function filterValidationValidatorStringLengthValidateMaximumWithCustomMessage(IntegrationTester $I)
    {
        $I->wantToTest(
            'Validation\Validator\StringLength :: validate() - maximum custom message'
        );

        $validation = new Validation();

        $validation->add(
            'message',
            new StringLength(
                [
                    'max'            => 4,
                    'messageMaximum' => 'The message is too long',
                ]
            )
        );

        $messages = $validation->validate(
            [
                'message' => 'Pet',
            ]
        );

        $I->assertSame(
            0,
            $messages->count()
        );


        $expected = new Messages(
            [
                new Message(
                    'The message is too long',
                    'message',
                    Max::class,
                    0
                ),
            ]
        );

        $messages = $validation->validate(
            [
                'message' => 'Validation',
            ]
        );

        $I->assertEquals($expected, $messages);
    }

    /**
     * Tests Phalcon\Filter\Validation\Validator\StringLength :: validate()
     * multiple field and single min, max
     *
     * @author Wojciech Ślawski <jurigag@gmail.com>
     * @since  2016-06-05
     */
    public function filterValidationValidatorStringLengthValidateMultipleFieldSingleMinMax(IntegrationTester $I)
    {
        $I->wantToTest(
            'Validation\Validator\StringLength :: validate() - multiple field and single min, max'
        );

        $validation = new Validation();

        $validationMinimumMessages = [
            'name' => 'Name length must be minimum 0.',
            'type' => 'Type length must be minimum 0.',
        ];

        $validationMaximumMessages = [
            'name' => 'Name length must be maximum 9.',
            'type' => 'Type length must be maximum 9.',
        ];

        $validation->add(
            [
                'name',
                'type',
            ],
            new StringLength(
                [
                    'min'            => 0,
                    'max'            => 9,
                    'messageMinimum' => $validationMinimumMessages,
                    'messageMaximum' => $validationMaximumMessages,
                ]
            )
        );


        $messages = $validation->validate(
            [
                'name' => 'SomeValue',
                'type' => 'SomeValue',
            ]
        );

        $I->assertSame(
            0,
            $messages->count()
        );


        $messages = $validation->validate(
            [
                'name' => 'SomeValue123',
                'type' => 'SomeValue',
            ]
        );

        $I->assertSame(
            1,
            $messages->count()
        );

        $I->assertSame(
            $validationMaximumMessages['name'],
            $messages->offsetGet(0)->getMessage()
        );


        $messages = $validation->validate(
            [
                'name' => 'SomeValue123',
                'type' => 'SomeValue123',
            ]
        );

        $I->assertSame(
            2,
            $messages->count()
        );

        $I->assertSame(
            $validationMaximumMessages['name'],
            $messages->offsetGet(0)->getMessage()
        );

        $I->assertSame(
            $validationMaximumMessages['type'],
            $messages->offsetGet(1)->getMessage()
        );
    }

    /**
     * Tests Phalcon\Filter\Validation\Validator\StringLength :: validate()
     * multiple field and min, max
     *
     * @author Wojciech Ślawski <jurigag@gmail.com>
     * @since  2016-06-05
     */
    public function filterValidationValidatorStringLengthValidateMultipleFieldMultipleMinMax(IntegrationTester $I)
    {
        $I->wantToTest('Validation\Validator\StringLength :: validate() - multiple field and min, max');

        $validation = new Validation();

        $validationMinimumMessages = [
            'name' => 'Name length must be minimum 0.',
            'type' => 'Type length must be minimum 0.',
        ];

        $validationMaximumMessages = [
            'name' => 'Name length must be maximum 9.',
            'type' => 'Type length must be maximum 4.',
        ];

        $validation->add(
            [
                'name',
                'type',
            ],
            new StringLength(
                [
                    'min'            => [
                        'name' => 0,
                        'type' => 0,
                    ],
                    'max'            => [
                        'name' => 9,
                        'type' => 4,
                    ],
                    'messageMinimum' => $validationMinimumMessages,
                    'messageMaximum' => $validationMaximumMessages,
                ]
            )
        );


        $messages = $validation->validate(
            [
                'name' => 'SomeValue',
                'type' => 'Some',
            ]
        );

        $I->assertSame(
            0,
            $messages->count()
        );


        $messages = $validation->validate(
            [
                'name' => 'SomeValue123',
                'type' => 'Some',
            ]
        );

        $I->assertSame(
            1,
            $messages->count()
        );

        $I->assertSame(
            $validationMaximumMessages['name'],
            $messages->offsetGet(0)->getMessage()
        );


        $messages = $validation->validate(
            [
                'name' => 'SomeValue',
                'type' => 'SomeValue',
            ]
        );

        $I->assertSame(
            1,
            $messages->count()
        );

        $I->assertSame(
            $validationMaximumMessages['type'],
            $messages->offsetGet(0)->getMessage()
        );


        $messages = $validation->validate(
            [
                'name' => 'SomeValue123',
                'type' => 'SomeValue',
            ]
        );

        $I->assertSame(
            2,
            $messages->count()
        );

        $I->assertSame(
            $validationMaximumMessages['name'],
            $messages->offsetGet(0)->getMessage()
        );

        $I->assertSame(
            $validationMaximumMessages['type'],
            $messages->offsetGet(1)->getMessage()
        );
    }
}
