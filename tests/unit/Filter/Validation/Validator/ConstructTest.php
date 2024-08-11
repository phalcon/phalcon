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

namespace Phalcon\Tests\Unit\Filter\Validation\Validator;

use Phalcon\Filter\Validation\Validator\PresenceOf;
use Phalcon\Filter\Validation\Validator\StringLength;
use Phalcon\Filter\Validation\ValidatorCompositeInterface;
use Phalcon\Filter\Validation\ValidatorInterface;
use Phalcon\Tests\Fixtures\Traits\ValidationTrait;
use Phalcon\Tests\AbstractUnitTestCase;

final class ConstructTest extends AbstractUnitTestCase
{
    use ValidationTrait;

    /**
     * Tests Phalcon\Filter\Validation\Validator :: __construct()
     *
     * @dataProvider getClasses
     *
     * @author       Phalcon Team <team@phalcon.io>
     * @since        2018-11-13
     */
    public function testFilterValidationValidatorAlnumConstruct(
        string $class
    ): void {
        $validator = new $class();

        $this->assertInstanceOf(
            ValidatorInterface::class,
            $validator
        );
    }

    /**
     * Tests Phalcon\Filter\Validation\Validator :: __construct()
     * with message option
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2018-11-13
     */
    public function testFilterValidationValidatorConstruct(): void
    {
        $message = uniqid('mes-');
        $default = new PresenceOf();                        // default message
        $custom  = new PresenceOf(['message' => $message]); // custom message

        // expected: null - empty message for default text (not set)
        $actual = $default->getOption('message');
        $this->assertNull($actual);

        // expected: text message - has custom message (developer set this message)
        $expected = $message;
        $actual   = $custom->getOption('message');
        $this->assertSame($expected, $actual);

        $custom   = new PresenceOf(['template' => $message]); // custom message
        $expected = $message;
        $actual   = $custom->getOption('message');
        $this->assertSame($expected, $actual);

        $custom   = new PresenceOf([$message]); // custom message
        $expected = $message;
        $actual   = $custom->getOption('message');
        $this->assertSame($expected, $actual);
    }

    /**
     * Tests Phalcon\Filter\Validation\Validator\StringLength :: __construct()
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2018-11-13
     */
    public function testFilterValidationValidatorStringLengthConstruct(): void
    {
        $validator = new StringLength();

        $this->assertInstanceOf(
            ValidatorCompositeInterface::class,
            $validator,
            'StringLength implements ValidatorCompositeInterface'
        );
    }
}
