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

namespace Phalcon\Filter\Validation\Validator\StringLength;

use Phalcon\Filter\Validation;
use Phalcon\Filter\Validation\AbstractValidator;
use Phalcon\Filter\Validation\Exception;
use Phalcon\Messages\Message;

use function function_exists;
use function is_array;
use function mb_strlen;
use function strlen;

/**
 * Validates that a string has the specified maximum constraints
 * The test is passed if for a string's length L, L<=max, i.e. L must
 * be at most max.
 *
 * ```php
 * use Phalcon\Filter\Validation;
 * use Phalcon\Filter\Validation\Validator\StringLength\Max;
 *
 * $validator = new Validation();
 *
 * $validation->add(
 *     "name_last",
 *     new Max(
 *         [
 *             "max"      => 50,
 *             "message"  => "We don't like really long names",
 *             "included" => true
 *         ]
 *     )
 * );
 *
 * $validation->add(
 *     [
 *         "name_last",
 *         "name_first",
 *     ],
 *     new Max(
 *         [
 *             "max" => [
 *                 "name_last"  => 50,
 *                 "name_first" => 40,
 *             ],
 *             "message" => [
 *                 "name_last"  => "We don't like really long last names",
 *                 "name_first" => "We don't like really long first names",
 *             ],
 *             "included" => [
 *                 "name_last"  => false,
 *                 "name_first" => true,
 *             ]
 *         ]
 *     )
 * );
 * ```
 */
class Max extends AbstractValidator
{
    /**
     * @var string|null
     */
    protected string | null $template = "Field :field must not exceed :max characters long";

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

        // Check if mbstring is available to calculate the correct length
        if (function_exists("mb_strlen")) {
            $length = mb_strlen((string)$value);
        } else {
            $length = strlen((string)$value);
        }

        $maximum = $this->getOption("max");

        if (is_array($maximum)) {
            $maximum = $maximum[$field];
        }

        $included = $this->getOption("included");

        if (is_array($included)) {
            $included = (bool)$included[$field];
        } else {
            $included = (bool)$included;
        }

        if ($included) {
            $failed = $length > $maximum;
        } else {
            $failed = $length >= $maximum;
        }

        if ($failed) {
            $replacePairs = [
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
