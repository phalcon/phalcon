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

use Phalcon\Contracts\Filter\FilterTypes;
use Phalcon\Filter\Validation;
use Phalcon\Filter\Validation\AbstractValidator;
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
 *
 * @phpstan-import-type filter_validator_options from FilterTypes
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
     * @phpstan-param filter_validator_options $options
     */
    public function __construct(array $options = [])
    {
        parent::__construct($options);
    }

    /**
     * Executes the validation
     */
    public function validate(Validation $validation, mixed $field): bool
    {
        /**
         * Validation iterates its validators by field name, so the field is
         * a string here. The parameter is mixed to match the untyped Zephir
         * signature.
         *
         * @var string $field
         */
        $fieldWith = $this->getOption("with");

        if (is_array($fieldWith)) {
            $fieldWith = $fieldWith[$field];
        }

        /**
         * The "with" option names the field to compare against.
         *
         * @var string $fieldWith
         */

        $value     = $validation->getValue($field);
        $valueWith = $validation->getValue($fieldWith);

        if (
            $this->rejectNonStringable($validation, $field, $value)
            || $this->rejectNonStringable($validation, $field, $valueWith)
        ) {
            return false;
        }

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
