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

namespace Phalcon\Tests\Unit\Filter\Validation\Validator\Alpha;

use Codeception\Example;
use Phalcon\Tests\UnitTestCase;
use Phalcon\Filter\Validation;
use Phalcon\Filter\Validation\Exception;
use Phalcon\Filter\Validation\Validator\Alpha;
use Phalcon\Messages\Message;
use Phalcon\Messages\Messages;
use stdClass;

final class ValidateTest extends UnitTestCase
{
    /**
     * Tests Phalcon\Filter\Validation\Validator\Alpha :: validate() - empty
     *
     * @return void
     * @throws Exception
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2023-08-03
     */
    public function testFilterValidationValidatorAlphaValidateEmpty(): void
    {
        $validation = new Validation();
        $validator  = new Alpha(['allowEmpty' => true,]);
        $validation->add('name', $validator);
        $entity       = new stdClass();
        $entity->name = '';

        $validation->bind($entity, []);
        $result = $validator->validate($validation, 'name');
        $this->assertTrue($result);
    }

    /**
     * Tests Phalcon\Filter\Validation\Validator\Alpha :: validate() - single field
     *
     * @author Wojciech Ślawski <jurigag@gmail.com>
     * @since  2016-06-05
     */
    public function testFilterValidationValidatorAlphaValidateSingleField(): void
    {
        $validation = new Validation();

        $validation->add(
            'name',
            new Alpha()
        );


        $messages = $validation->validate(
            [
                'name' => 'Asd',
            ]
        );

        $this->assertSame(
            0,
            $messages->count()
        );


        $messages = $validation->validate(
            [
                'name' => 'Asd123',
            ]
        );

        $this->assertSame(
            1,
            $messages->count()
        );
    }

    /**
     * Tests Phalcon\Filter\Validation\Validator\Alpha :: validate() - multiple field
     *
     * @author Wojciech Ślawski <jurigag@gmail.com>
     * @since  2016-06-05
     */
    public function testFilterValidationValidatorAlphaValidateMultipleField(): void
    {
        $validation = new Validation();

        $validationMessages = [
            'name' => 'Name must be alpha.',
            'type' => 'Type must by alpha.',
        ];

        $validation->add(
            ['name', 'type'],
            new Alpha(
                [
                    'message' => $validationMessages,
                ]
            )
        );


        $messages = $validation->validate(
            [
                'name' => 'Asd',
                'type' => 'Asd',
            ]
        );

        $this->assertSame(
            0,
            $messages->count()
        );


        $messages = $validation->validate(
            [
                'name' => 'Asd123',
                'type' => 'Asd',
            ]
        );

        $this->assertSame(
            1,
            $messages->count()
        );

        $this->assertSame(
            $validationMessages['name'],
            $messages->offsetGet(0)->getMessage()
        );


        $messages = $validation->validate(
            [
                'name' => 'Asd123',
                'type' => 'Asd123',
            ]
        );

        $this->assertSame(
            2,
            $messages->count()
        );

        $this->assertSame(
            $validationMessages['name'],
            $messages->offsetGet(0)->getMessage()
        );

        $this->assertSame(
            $validationMessages['type'],
            $messages->offsetGet(1)->getMessage()
        );
    }

    /**
     * Tests Phalcon\Filter\Validation\Validator\Alpha :: validate() - Non Alphabetic
     * Characters
     *
     * @author       Phalcon Team <team@phalcon.io>
     * @since        2016-06-10
     *
     * @dataProvider nonAlphaProvider
     */
    public function testFilterValidationValidatorAlphaValidateNonAlphabeticCharacters(
        int|string $input
    ): void {
        $validation = new Validation();

        $validation->add(
            'name',
            new Alpha(
                [
                    'message' => ':field must contain only letters',
                ]
            )
        );

        $expected = new Messages(
            [
                new Message(
                    'name must contain only letters',
                    'name',
                    Alpha::class,
                    0
                ),
            ]
        );

        $actual = $validation->validate(
            [
                'name' => $input,
            ]
        );

        $this->assertEquals($expected, $actual);
    }

    /**
     * Tests Phalcon\Filter\Validation\Validator\Alpha :: validate() - Alphabetic
     * Characters
     *
     * @author       Phalcon Team <team@phalcon.io>
     * @since        2016-06-10
     *
     * @dataProvider alphaProvider
     */
    public function testFilterValidationValidatorAlphaValidateAlphabeticCharacters(
        ?string $input
    ): void {
        $validation = new Validation();

        $validation->add(
            'name',
            new Alpha(
                [
                    'message' => ':field must contain only letters',
                ]
            )
        );

        $messages = $validation->validate(
            [
                'name' => $input,
            ]
        );

        $this->assertSame(
            0,
            $messages->count()
        );
    }

    /**
     * Tests Phalcon\Filter\Validation\Validator\Alpha :: validate() - Non Latin
     * Characters
     *
     * @author       Phalcon Team <team@phalcon.io>
     * @since        2016-06-10
     *
     * @dataProvider nonLatinCharactersProvider
     */
    public function testFilterValidationValidatorAlphaValidateNonLatinCharacters(
        string $input
    ): void {
        $validation = new Validation();

        $validation->add(
            'name',
            new Alpha(
                [
                    'message' => ':field must contain only letters',
                ]
            )
        );

        $messages = $validation->validate(
            [
                'name' => $input,
            ]
        );

        $this->assertSame(
            0,
            $messages->count()
        );
    }

    public static function nonAlphaProvider(): array
    {
        return [
            ['1'],
            [123],
            ['a-b-c-d'],
            ['a-1-c-2'],
            ['a1c2'],
            ['o0o0o0o0'],
        ];
    }

    public static function alphaProvider(): array
    {
        return [
            ['a'],
            ['asdavafaiwnoabwiubafpowf'],
            ['QWERTYUIOPASDFGHJKL'],
            ['aSdFgHjKl'],
            [null],
        ];
    }

    public static function nonLatinCharactersProvider(): array
    {
        return [
            ['йцукенг'],
            ['ждлорпа'],
            ['Señor'],
            ['cocoñùт'],
            ['COCOÑÙТ'],
            ['JÄGER'],
            ['šš'],
            ['あいうえお'],
            ['零一二三四五'],
        ];
    }
}
