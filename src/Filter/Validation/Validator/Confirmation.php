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
use Phalcon\Filter\Validation\Exception;
use Phalcon\Filter\Validation\Exceptions\MissingMbstring;
use Phalcon\Messages\Message;
use Phalcon\Traits\Php\InfoTrait;

use function is_array;
use function mb_strtolower;
use function strcmp;

/**
 * Checks that two values have the same value
 *
 * ```php
 * use Phalcon\Filter\Validation;
 * use Phalcon\Filter\Validation\Validator\Confirmation;
 *
 * $validator = new Validation();
 *
 * $validator->add(
 *     "password",
 *     new Confirmation(
 *         [
 *             "message" => "Password does not match confirmation",
 *             "with"    => "confirmPassword",
 *         ]
 *     )
 * );
 *
 * $validator->add(
 *     [
 *         "password",
 *         "email",
 *     ],
 *     new Confirmation(
 *         [
 *             "message" => [
 *                 "password" => "Password does not match confirmation",
 *                 "email"    => "Email does not match confirmation",
 *             ],
 *             "with" => [
 *                 "password" => "confirmPassword",
 *                 "email"    => "confirmEmail",
 *             ],
 *         ]
 *     )
 * );
 * ```
 */
class Confirmation extends AbstractValidator
{
    use InfoTrait;

    /**
     * @var string|null
     */
    protected string | null $template = "Field :field must be the same as :with";

    /**
     * Constructor
     *
     * @param array $options
     */
    public function __construct(array $options = [])
    {
        parent::__construct($options);
    }

    /**
     * Executes the validation
     *
     * @param Validation $validation
     * @param string     $field
     *
     * @return bool
     */
    public function validate(Validation $validation, string $field): bool
    {
        $fieldWith = $this->getOption("with");

        if (is_array($fieldWith)) {
            $fieldWith = $fieldWith[$field];
        }

        $value     = $validation->getValue($field);
        $valueWith = $validation->getValue($fieldWith);

        if (!$this->compare((string)$value, (string)$valueWith)) {
            $labelWith = $this->getOption("labelWith");

            if (is_array($labelWith)) {
                $labelWith = $labelWith[$fieldWith];
            }

            if (empty($labelWith)) {
                $labelWith = $validation->getLabel($fieldWith);
            }

            $replacePairs = [
                ":with" => $labelWith,
            ];

            $validation->appendMessage(
                $this->messageFactory($validation, $field, $replacePairs)
            );

            return false;
        }

        return true;
    }

    /**
     * Compare strings
     *
     * @param string $a
     * @param string $b
     *
     * @return bool
     */
    final protected function compare(string $a, string $b): bool
    {
        if ($this->getOption("ignoreCase", false)) {
            /**
             * mbstring is required here
             */
            if (!$this->phpFunctionExists("mb_strtolower")) {
                throw new MissingMbstring();
            }

            $a = mb_strtolower($a, "utf-8");
            $b = mb_strtolower($b, "utf-8");
        }

        return strcmp($a, $b) === 0;
    }
}
