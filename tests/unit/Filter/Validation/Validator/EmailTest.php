<?php

/**
 * This file is part of the Phalcon Framework.
 *
 * (c) Phalcon Team <team@phalcon.io>
 *
 * For the full copyright and license information, please view the
 * LICENSE.txt file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Phalcon\Tests\Unit\Filter\Validation\Validator;

use Phalcon\Filter\Validation;
use Phalcon\Tests\AbstractUnitTestCase;

final class EmailTest extends AbstractUnitTestCase
{
    /**
     * Tests Filter\Validation\Validator\Email :: validate - valid email
     *
     * @author n[oO]ne <lominum@protonmail.com>
     * @since  2024-08-19
     */
    public function testFilterValidationValidatorEmailValidEmail(): void
    {
        $validation = new Validation();
        $validation->add('email', new Validation\Validator\Email());

        $this->assertEmpty($validation->validate(['email' => 'test@example.com']));
    }

    /**
     * Tests Filter\Validation\Validator\Email :: validate - invalid email
     *
     * @author n[oO]ne <lominum@protonmail.com>
     * @since  2024-08-19
     */
    public function testFilterValidationValidatorEmailInvalidEmail(): void
    {
        $validation = new Validation();
        $validation->add('email', new Validation\Validator\Email());

        $this->assertNotEmpty($validation->validate(['email' => 'test@-example.com']));
    }

    /**
     * Tests Filter\Validation\Validator\Email :: validate - without utf8 is ok
     *
     * @author n[oO]ne <lominum@protonmail.com>
     * @since  2024-08-19
     */
    public function testFilterValidationValidatorEmailWithoutUTF8Success(): void
    {
        $validation = new Validation();
        $validation->add('email', new Validation\Validator\Email());

        $this->assertEmpty($validation->validate(['email' => 'test@example.com']));
    }

    /**
     * Tests Filter\Validation\Validator\Email :: validate - empty is not ok
     *
     * @author n[oO]ne <lominum@protonmail.com>
     * @since  2024-08-19
     */
    public function testFilterValidationValidatorEmailAllowEmptyFails(): void
    {
        $validation = new Validation();
        $validation->add('email', new Validation\Validator\Email());

        $this->assertNotEmpty($validation->validate(['email' => '']));
    }

    /**
     * Tests Filter\Validation\Validator\Email :: validate - empty is ok
     *
     * @author n[oO]ne <lominum@protonmail.com>
     * @since  2024-08-19
     */
    public function testFilterValidationValidatorEmailAllowEmptySuccess(): void
    {
        $validation = new Validation();
        $validation->add('email', new Validation\Validator\Email(['allowEmpty' => true]));

        $this->assertEmpty($validation->validate(['email' => '']));
    }

    /**
     * Tests Filter\Validation\Validator\Email :: validate - with utf8 fails
     *
     * @author n[oO]ne <lominum@protonmail.com>
     * @since  2024-08-19
     */
    public function testFilterValidationValidatorEmailWithUTF8Fail(): void
    {
        $validation = new Validation();
        $validation->add('email', new Validation\Validator\Email());

        $this->assertNotEmpty($validation->validate(['email' => 'täst@example.com']));
    }

    /**
     * Tests Filter\Validation\Validator\Email :: validate - with utf8 success
     *
     * @author n[oO]ne <lominum@protonmail.com>
     * @since  2024-08-19
     */
    public function testFilterValidationValidatorEmailWithUTF8Success(): void
    {
        $validation = new Validation();
        $validation->add('email', new Validation\Validator\Email(['allowUTF8' => true]));

        $this->assertEmpty($validation->validate(['email' => 'täst@example.com']));
    }
}
