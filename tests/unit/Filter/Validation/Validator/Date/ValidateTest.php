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

namespace Phalcon\Tests\Unit\Filter\Validation\Validator\Date;

use Phalcon\Filter\Validation;
use Phalcon\Filter\Validation\Exception;
use Phalcon\Filter\Validation\Validator\Date;
use Phalcon\Messages\Message;
use Phalcon\Messages\Messages;
use Phalcon\Tests\AbstractUnitTestCase;
use stdClass;

final class ValidateTest extends AbstractUnitTestCase
{
    public static function getInvalidDates(): array
    {
        return [
            ['', 'Y-m-d'],
            [false, 'Y-m-d'],
            [null, 'Y-m-d'],
            [new stdClass(), 'Y-m-d'],
            ['2015-13-01', 'Y-m-d'],
            ['2015-01-32', 'Y-m-d'],
            ['2015-01', 'Y-m-d'],
            ['2015-01-01', 'd-m-Y'],
        ];
    }

    public static function getValidDates(): array
    {
        return [
            ['2012-01-01', 'Y-m-d'],
            ['2013-31-12', 'Y-d-m'],
            ['01/01/2014', 'd/m/Y'],
            ['12@12@2015', 'd@m@Y'],
        ];
    }

    /**
     * Tests detect invalid dates
     *
     * @author       Gustavo Verzola <verzola@gmail.com>
     * @since        2015-03-09
     *
     * @dataProvider getInvalidDates
     */
    public function testFilterValidationValidatorDateDetectInvalidDates(
        mixed $date,
        string $format
    ): void {
        $validation = new Validation();

        $validation->add(
            'date',
            new Date(
                [
                    'format' => $format,
                ]
            )
        );

        $expected = new Messages(
            [
                new Message(
                    'Field date is not a valid date',
                    'date',
                    Date::class,
                    0
                ),
            ]
        );

        $actual = $validation->validate(
            [
                'date' => $date,
            ]
        );

        $this->assertEquals($expected, $actual);
    }

    /**
     * Tests detect valid dates
     *
     * @author       Gustavo Verzola <verzola@gmail.com>
     * @since        2015-03-09
     *
     * @dataProvider getValidDates
     */
    public function testFilterValidationValidatorDateDetectValidDates(
        string $date,
        string $format
    ): void {
        $validation = new Validation();

        $validation->add(
            'date',
            new Date(
                [
                    'format' => $format,
                ]
            )
        );

        $messages = $validation->validate(
            [
                'date' => $date,
            ]
        );

        $this->assertSame(
            0,
            $messages->count()
        );
    }

    /**
     * Tests date validator with multiple field
     *
     * @author Wojciech Ślawski <jurigag@gmail.com>
     * @since  2016-06-05
     */
    public function testFilterValidationValidatorDateMultipleField(): void
    {
        $validation = new Validation();

        $validationMessages = [
            'date'        => 'Date must be correct date format Y-m-d.',
            'anotherDate' => 'AnotherDate must be correct date format d-m-Y.',
        ];

        $validation->add(
            ['date', 'anotherDate'],
            new Date(
                [
                    'format'  => [
                        'date'        => 'Y-m-d',
                        'anotherDate' => 'd-m-Y',
                    ],
                    'message' => $validationMessages,
                ]
            )
        );

        $messages = $validation->validate(
            [
                'date'        => '2016-06-05',
                'anotherDate' => '05-06-2017',
            ]
        );

        $this->assertSame(
            0,
            $messages->count()
        );

        $messages = $validation->validate(
            [
                'date'        => '2016-06-32',
                'anotherDate' => '05-06-2017',
            ]
        );

        $this->assertSame(
            1,
            $messages->count()
        );

        $expected = $validationMessages['date'];
        $actual   = $messages->offsetGet(0)->getMessage();
        $this->assertSame($expected, $actual);

        $messages = $validation->validate(
            [
                'date'        => '2016-06-32',
                'anotherDate' => '32-06-2017',
            ]
        );

        $this->assertSame(
            2,
            $messages->count()
        );

        $expected = $validationMessages['date'];
        $actual   = $messages->offsetGet(0)->getMessage();
        $this->assertSame($expected, $actual);

        $expected = $validationMessages['anotherDate'];
        $actual   = $messages->offsetGet(1)->getMessage();
        $this->assertSame($expected, $actual);
    }

    /**
     * Tests date validator with single field
     *
     * @author Wojciech Ślawski <jurigag@gmail.com>
     * @since  2016-06-05
     */
    public function testFilterValidationValidatorDateSingleField(): void
    {
        $validation = new Validation();

        $validation->add(
            'date',
            new Date()
        );


        $messages = $validation->validate(
            [
                'date' => '2016-06-05',
            ]
        );

        $this->assertSame(
            0,
            $messages->count()
        );


        $messages = $validation->validate(
            [
                'date' => '2016-06-32',
            ]
        );

        $this->assertSame(
            1,
            $messages->count()
        );
    }

    /**
     * Tests Phalcon\Filter\Validation\Validator\Date :: validate() - empty
     *
     * @return void
     *
     * @return void
     * @throws Exception
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2023-08-03
     */
    public function testFilterValidationValidatorDateValidateEmpty(): void
    {
        $validation = new Validation();
        $validator  = new Date(['allowEmpty' => true,]);
        $validation->add('start_date', $validator);
        $entity             = new stdClass();
        $entity->start_date = '';

        $validation->bind($entity, []);
        $result = $validator->validate($validation, 'start_date');
        $this->assertTrue($result);
    }

    /**
     * @return void
     *
     * @return void
     * @throws Exception
     *
     * @author Phalcon Team <tram@phalcon.io>
     * @since  2023-08-03
     */
    public function testFilterValidationValidatorDateValidateMultipleField(): void
    {
        $validation = new Validation();

        $validationMessages = [
            'start_date' => 'start_date must be date.',
            'end_date'   => 'end_date must by date.',
        ];

        $validation->add(
            ['start_date', 'end_date'],
            new Date(
                [
                    'message' => $validationMessages,
                ]
            )
        );

        $messages = $validation->validate(
            [
                'start_date' => '2022-12-31',
                'end_date'   => '2023-01-01',
            ]
        );

        $expected = 0;
        $actual   = $messages->count();
        $this->assertSame($expected, $actual);


        $messages = $validation->validate(
            [
                'start_date' => '20230101',
                'end_date'   => '2022-12-31',
            ]
        );

        $expected = 1;
        $actual   = $messages->count();
        $this->assertSame($expected, $actual);

        $expected = $validationMessages['start_date'];
        $actual   = $messages->offsetGet(0)->getMessage();
        $this->assertSame($expected, $actual);

        $messages = $validation->validate(
            [
                'start_date' => '20230101',
                'end_date'   => '20230101',
            ]
        );

        $expected = 2;
        $actual   = $messages->count();
        $this->assertSame($expected, $actual);

        $expected = $validationMessages['start_date'];
        $actual   = $messages->offsetGet(0)->getMessage();
        $this->assertSame($expected, $actual);

        $expected = $validationMessages['end_date'];
        $actual   = $messages->offsetGet(1)->getMessage();
        $this->assertSame($expected, $actual);
    }

    /**
     * Tests Phalcon\Filter\Validation\Validator\Date :: validate() - single field
     *
     * @return void
     *
     * @return void
     * @throws Exception
     *
     * @author Phalcon Team <tram@phalcon.io>
     * @since  2023-08-03
     */
    public function testFilterValidationValidatorDateValidateSingleField(): void
    {
        $validation = new Validation();
        $validation->add('start_date', new Date());

        $messages = $validation->validate(
            [
                'start_date' => '2022-12-31',
            ]
        );

        $expected = 0;
        $actual   = $messages->count();
        $this->assertSame($expected, $actual);

        $messages = $validation->validate(
            [
                'start_date' => '19450101',
            ]
        );

        $expected = 1;
        $actual   = $messages->count();
        $this->assertSame($expected, $actual);
    }
}
