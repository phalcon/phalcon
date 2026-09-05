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

use DateTime;
use Phalcon\Contracts\Filter\FilterTypes;
use Phalcon\Filter\Validation;
use Phalcon\Filter\Validation\AbstractValidator;

use function is_string;

/**
 * Checks if a value is a valid date
 *
 * ```php
 * use Phalcon\Filter\Validation;
 * use Phalcon\Filter\Validation\Validator\Date as DateValidator;
 *
 * $validator = new Validation();
 *
 * $validator->add(
 *     "date",
 *     new DateValidator(
 *         [
 *             "format"  => "d-m-Y",
 *             "message" => "The date is invalid",
 *         ]
 *     )
 * );
 *
 * $validator->add(
 *     [
 *         "date",
 *         "anotherDate",
 *     ],
 *     new DateValidator(
 *         [
 *             "format" => [
 *                 "date"        => "d-m-Y",
 *                 "anotherDate" => "Y-m-d",
 *             ],
 *             "message" => [
 *                 "date"        => "The date is invalid",
 *                 "anotherDate" => "The another date is invalid",
 *             ],
 *         ]
 *     )
 * );
 * ```
 *
 * @phpstan-import-type filter_validator_options from FilterTypes
 */
class Date extends AbstractValidator
{
    protected string | null $template = "Field :field is not a valid date";

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
        $value = $validation->getValue($field);
        if ($this->allowEmpty($field, $value)) {
            return true;
        }

        /** @phpstan-var array<string, string>|string $format */
        $format = $this->getOption("format");
        if (is_array($format)) {
            $format = $format[$field];
        }

        if (empty($format)) {
            $format = "Y-m-d";
        }

        if (!$this->checkDate($value, $format)) {
            $validation->appendMessage(
                $this->messageFactory($validation, $field)
            );

            return false;
        }

        return true;
    }

    /**
     * @phpstan-param string $format
     */
    private function checkDate(mixed $value, mixed $format): bool
    {
        if (!is_string($value)) {
            return false;
        }

        $date   = DateTime::createFromFormat($format, $value);
        $errors = DateTime::getLastErrors();

        return $errors === false || ($errors["warning_count"] == 0 && $errors["error_count"] == 0);
    }
}
