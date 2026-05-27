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

use function array_reverse;
use function array_sum;
use function restore_error_handler;
use function set_error_handler;
use function str_split;

use const E_WARNING;

/**
 * Checks if a value has a valid credit card number
 *
 * ```php
 * use Phalcon\Filter\Validation;
 * use Phalcon\Filter\Validation\Validator\CreditCard as CreditCardValidator;
 *
 * $validator = new Validation();
 *
 * $validator->add(
 *     "creditCard",
 *     new CreditCardValidator(
 *         [
 *             "message" => "The credit card number is not valid",
 *         ]
 *     )
 * );
 *
 * $validator->add(
 *     [
 *         "creditCard",
 *         "secondCreditCard",
 *     ],
 *     new CreditCardValidator(
 *         [
 *             "message" => [
 *                 "creditCard"       => "The credit card number is not valid",
 *                 "secondCreditCard" => "The second credit card number is not valid",
 *             ],
 *         ]
 *     )
 * );
 * ```
 */
class CreditCard extends AbstractValidator
{
    /**
     * @var string|null
     */
    protected string | null $template = "Field :field is not valid for a credit card number";

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
        $value = $validation->getValue($field);
        if ($this->allowEmpty($field, $value)) {
            return true;
        }

        $valid = $this->verifyByLuhnAlgorithm((string)$value);

        if (!$valid) {
            $validation->appendMessage(
                $this->messageFactory($validation, $field)
            );

            return false;
        }

        return true;
    }

    /**
     * is a simple checksum formula used to validate a variety of identification
     * numbers
     *
     * @param string $number
     *
     * @return bool
     */
    private function verifyByLuhnAlgorithm(string $number): bool
    {
        if (ctype_digit($number) === false) {
            return false;
        }

        $hash   = "";
        $digits = array_reverse(str_split($number));

        foreach ($digits as $position => $digit) {
            $hash .= ($position % 2 ? $digit * 2 : $digit);
        }

        $result = array_sum(str_split($hash));

        return ($result % 10 == 0);
    }
}
