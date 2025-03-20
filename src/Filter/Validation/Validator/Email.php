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

namespace Phalcon\Filter\Validation\Validator;

use Phalcon\Filter\Validation;
use Phalcon\Filter\Validation\AbstractValidator;

use function filter_var;

use const FILTER_DEFAULT;
use const FILTER_FLAG_EMAIL_UNICODE;
use const FILTER_VALIDATE_EMAIL;

/**
 * Checks if a value has a correct e-mail format
 *
 * ```php
 * use Phalcon\Filter\Validation;
 * use Phalcon\Filter\Validation\Validator\Email as EmailValidator;
 *
 * $validator = new Validation();
 *
 * $validator->add(
 *     "email",
 *     new EmailValidator(
 *         [
 *             "message" => "The e-mail is not valid",
 *         ]
 *     )
 * );
 *
 * $validator->add(
 *     [
 *         "email",
 *         "anotherEmail",
 *     ],
 *     new EmailValidator(
 *         [
 *             "message" => [
 *                 "email"        => "The e-mail is not valid",
 *                 "anotherEmail" => "The another e-mail is not valid",
 *             ],
 *         ]
 *     )
 * );
 * ```
 *
 *
 * $validator->add(
 *     "täst@example.com",
 *     new EmailValidator(
 *         [
 *             "message" => "The e-mail is not valid",
 *             "allowUTF8" => true,
 *         ]
 *     )
 * );
 * ```
 */
class Email extends AbstractValidator
{
    /**
     * @var string|null
     */
    protected string | null $template = "Field :field must be an email address";

    /**
     * Executes the validation
     *
     * @param Validation $validation
     * @param string     $field
     *
     * @return bool
     * @throws \Phalcon\Di\Exception
     */
    public function validate(Validation $validation, string $field): bool
    {
        $value = $validation->getValue($field);
        if (true === $this->allowEmpty($field, $value)) {
            return true;
        }

        $flags = FILTER_DEFAULT;
        if ($this->getOption("allowUTF8")) {
            $flags = FILTER_FLAG_EMAIL_UNICODE;
        }

        if (!filter_var($value, FILTER_VALIDATE_EMAIL, $flags)) {
            $validation->appendMessage(
                $this->messageFactory($validation, $field)
            );

            return false;
        }

        return true;
    }
}
