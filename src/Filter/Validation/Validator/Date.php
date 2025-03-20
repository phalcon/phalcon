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
 */
class Date extends AbstractValidator
{
    /**
     * @var string|null
     */
    protected string | null $template = "Field :field is not a valid date";

    /**
     * Executes the validation
     *
     * @param Validation $validation
     * @param string     $field
     *
     * @return bool
     * @throws Validation\Exception
     */
    public function validate(Validation $validation, string $field): bool
    {
        $value = $validation->getValue($field);
        if (true === $this->allowEmpty($field, $value)) {
            return true;
        }

        $format = $this->checkArray($this->getOption("format"), $field);

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
     * @param mixed  $value
     * @param string $format
     *
     * @return bool
     */
    private function checkDate(mixed $value, string $format): bool
    {
        if (!is_string($value)) {
            return false;
        }

        DateTime::createFromFormat($format, $value);
        $errors = DateTime::getLastErrors();

        $warningCount = $errors["warning_count"] ?? 0;
        $errorsCount  = $errors["error_count"] ?? 0;

        return 0 === $warningCount && 0 === $errorsCount;
    }
}
