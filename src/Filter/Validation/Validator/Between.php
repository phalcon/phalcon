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

use function is_array;

/**
 * Validates that a value is between an inclusive range of two values.
 * For a value x, the test is passed if minimum<=x<=maximum.
 *
 * ```php
 * use Phalcon\Filter\Validation;
 * use Phalcon\Filter\Validation\Validator\Between;
 *
 * $validator = new Validation();
 *
 * $validator->add(
 *     "price",
 *     new Between(
 *         [
 *             "minimum" => 0,
 *             "maximum" => 100,
 *             "message" => "The price must be between 0 and 100",
 *         ]
 *     )
 * );
 *
 * $validator->add(
 *     [
 *         "price",
 *         "amount",
 *     ],
 *     new Between(
 *         [
 *             "minimum" => [
 *                 "price"  => 0,
 *                 "amount" => 0,
 *             ],
 *             "maximum" => [
 *                 "price"  => 100,
 *                 "amount" => 50,
 *             ],
 *             "message" => [
 *                 "price"  => "The price must be between 0 and 100",
 *                 "amount" => "The amount must be between 0 and 50",
 *             ],
 *         ]
 *     )
 * );
 * ```
 */
class Between extends AbstractValidator
{
    /**
     * @var string|null
     */
    protected string | null $template = "Field :field must be within the range of :min to :max";

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
        $value   = $validation->getValue($field);
        $minimum = $this->getOption("minimum");
        $maximum = $this->getOption("maximum");

        if ($this->allowEmpty($field, $value)) {
            return true;
        }

        if (is_array($minimum)) {
            $minimum = $minimum[$field];
        }

        if (is_array($maximum)) {
            $maximum = $maximum[$field];
        }

        if ($value < $minimum || $value > $maximum) {
            $replacePairs = [
                ":min" => $minimum,
                ":max" => $maximum,
            ];

            $validation->appendMessage(
                $this->messageFactory($validation, $field, $replacePairs)
            );

            return false;
        }

        return true;
    }
}
