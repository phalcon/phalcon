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

namespace Phalcon\Tests\Unit\Filter\Validation;

use Phalcon\Filter\Validation;
use Phalcon\Filter\Validation\Validator\Alpha;
use Phalcon\Filter\Validation\Validator\Email;
use Phalcon\Filter\Validation\Validator\PresenceOf;
use Phalcon\Filter\Validation\Validator\StringLength;
use Phalcon\Filter\Validation\Validator\StringLength\Min;
use Phalcon\Filter\Validation\Validator\Url;
use Phalcon\Messages\Message;
use Phalcon\Messages\Messages;
use Phalcon\Tests\AbstractUnitTestCase;
use Phalcon\Tests\Support\Traits\DiTrait;
use Phalcon\Tests\Unit\Filter\Validation\Fake\FakeUsersModel;

final class ValidationTest extends AbstractUnitTestCase
{
    use DiTrait;

    private Validation $validation;

    public function setUp(): void
    {
        $this->markTestSkipped('Need to be fixed - db missing');
        $this->setNewFactoryDefault();
        $this->validation = new Validation();

        $this->validation->add(
            'name',
            new PresenceOf(
                [
                    'message' => 'Name cant be empty.',
                ]
            )
        );

        $this->validation->setFilters('name', 'trim');
    }

    /**
     * Tests validate method with entity and filters
     *
     * @author Wojciech Ślawski <jurigag@gmail.com>
     * @since  2016-09-26
     */
    public function testWithEntityAndFilter(): void
    {
        $users = new FakeUsersModel(
            [
                'name' => ' ',
            ]
        );

        $messages = $this->validation->validate(null, $users);

        $this->assertEquals(
            1,
            $messages->count()
        );

        $this->assertEquals(
            'Name cant be empty.',
            $messages->offsetGet(0)->getMessage()
        );

        $expectedMessages = new Messages(
            [
                new Message(
                    'Name cant be empty.',
                    'name',
                    PresenceOf::class,
                    0
                ),
            ]
        );

        $this->assertEquals($messages, $expectedMessages);
    }

    /**
     * Tests that filters in validation will correctly filter entity values
     *
     * @author Wojciech Ślawski <jurigag@gmail.com>
     * @since  2016-09-26
     */
    public function testFilteringEntity(): void
    {
        $users = new FakeUsersModel();

        $users->assign(
            [
                'name' => 'SomeName      ',
            ]
        );

        $this->validation->validate(null, $users);

        $this->assertEquals(
            'SomeName',
            $users->name
        );
    }

    public function testValidationFiltering(): void
    {
        $validation = new Validation();

        $validation->setDI(
            $this->container
        );

        $validation
            ->add(
                'name',
                new PresenceOf(
                    [
                        'message' => 'The name is required',
                    ]
                )
            )
            ->add(
                'email',
                new PresenceOf(
                    [
                        'message' => 'The email is required',
                    ]
                )
            )
        ;

        $validation->setFilters('name', 'trim');
        $validation->setFilters('email', 'trim');

        $messages = $validation->validate(
            [
                'name'  => '  ',
                'email' => '    ',
            ]
        );

        $this->assertCount(2, $messages);

        $filtered = $messages->filter('email');

        $expectedMessages = [
            new Message(
                'The email is required',
                'email',
                PresenceOf::class,
                0
            ),
        ];

        $this->assertEquals($filtered, $expectedMessages);
    }

    public function testValidationSetLabels(): void
    {
        $validation = new Validation();

        $validation->add(
            'email',
            new PresenceOf(
                [
                    'message' => 'The :field is required',
                ]
            )
        );

        $validation->add(
            'email',
            new Email(
                [
                    'message' => 'The :field must be email',
                    'label'   => 'E-mail',
                ]
            )
        );

        $validation->add(
            'firstname',
            new PresenceOf(
                [
                    'message' => 'The :field is required',
                ]
            )
        );

        $validation->add(
            'firstname',
            new StringLength(
                [
                    'min'            => 4,
                    'messageMinimum' => 'The :field is too short',
                ]
            )
        );

        $validation->setLabels(
            [
                'firstname' => 'First name',
            ]
        );

        $messages = $validation->validate(
            [
                'email'     => '',
                'firstname' => '',
            ]
        );

        $expectedMessages = new Messages(
            [
                new Message(
                    'The email is required',
                    'email',
                    PresenceOf::class,
                    0
                ),
                new Message(
                    'The E-mail must be email',
                    'email',
                    Email::class,
                    0
                ),
                new Message(
                    'The First name is required',
                    'firstname',
                    PresenceOf::class,
                    0
                ),
                new Message(
                    'The First name is too short',
                    'firstname',
                    Min::class,
                    0
                ),
            ]
        );

        $this->assertEquals($messages, $expectedMessages);
    }

    /**
     * Tests that empty values behaviour.
     *
     * @author Gorka Guridi <gorka.guridi@gmail.com>
     * @since  2016-12-30
     */
    public function testEmptyValues(): void
    {
        $validation = new Validation();

        $validation->setDI(
            $this->container
        );

        $validation
            ->add(
                'name',
                new Alpha(
                    [
                        'message' => 'The name is not valid',
                    ]
                )
            )
            ->add(
                'name',
                new PresenceOf(
                    [
                        'message' => 'The name is required',
                    ]
                )
            )
            ->add(
                'url',
                new Url(
                    [
                        'message'    => 'The url is not valid.',
                        'allowEmpty' => true,
                    ]
                )
            )
            ->add(
                'email',
                new Email(
                    [
                        'message'    => 'The email is not valid.',
                        'allowEmpty' => [null, false],
                    ]
                )
            )
        ;

        $messages = $validation->validate(
            [
                'name'  => '',
                'url'   => null,
                'email' => '',
            ]
        );

        $this->assertCount(2, $messages);


        $messages = $validation->validate(
            [
                'name'  => 'MyName',
                'url'   => '',
                'email' => '',
            ]
        );

        $this->assertCount(1, $messages);


        $messages = $validation->validate(
            [
                'name'  => 'MyName',
                'url'   => false,
                'email' => null,
            ]
        );

        $this->assertCount(0, $messages);


        $messages = $validation->validate(
            [
                'name'  => 'MyName',
                'url'   => 0,
                'email' => 0,
            ]
        );

        $this->assertCount(1, $messages);
    }
}
