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
use Phalcon\Filter\Validation\Validator\PresenceOf;
use Phalcon\Messages\Message;
use Phalcon\Messages\Messages;
use Phalcon\Tests\AbstractUnitTestCase;
use PHPUnit\Framework\Attributes\Test;
use stdClass;

use function date;

final class ValidateTest extends AbstractUnitTestCase
{
    /**
     * Tests Phalcon\Filter\Validation :: validate() - message to non object
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2016-06-27
     * @issue  10405
     */
    #[Test]
    public function testFilterValidationValidateException(): void
    {
        $data       = ['name' => 'Leonidas'];
        $validation = new Validation();
        $validator  = new Alpha();
        $entity     = new stdClass();
        $validation->add('name', $validator);
        $messages = $validation->validate($data, $entity);

        $this->assertCount(0, $messages);
    }

    /**
     * Tests Phalcon\Filter\Validation :: validate() - message to non object
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2016-06-27
     * @issue  10405
     */
    #[Test]
    public function testFilterValidationValidateMessageToNonObject(): void
    {
        $myValidator = new PresenceOf();
        $validation  = new Validation();

        $validation->bind(
            new stdClass(),
            [
                'day' => date('d'),
                'month' => date('m'),
                'year' => (string)(intval(date('Y')) + 1),
            ]
        );

        $myValidator->validate($validation, 'foo');

        $expected = new Messages(
            [
                new Message(
                    'Field foo is required',
                    'foo',
                    PresenceOf::class,
                    0
                ),
            ]
        );

        $actual = $validation->getMessages();
        $this->assertEquals($expected, $actual);
    }
}
