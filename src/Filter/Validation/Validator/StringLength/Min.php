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
use Phalcon\Traits\Php\InfoTrait;

use function is_array;
use function mb_strlen;
use function strlen;

/**
 * Validates that a string has the specified minimum constraints
 * The test is passed if for a string's length L, min<=L, i.e. L must
 * be at least min.
 *
 * ```php
 * use Phalcon\Filter\Validation;
 * use Phalcon\Filter\Validation\Validator\StringLength\Min;
 *
 * $validator = new Validation();
 *
 * $validation->add(
 *     "name_last",
 *     new Min(
 *         [
 *             "min"     => 2,
 *             "message" => "We want more than just their initials",
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
 *     new Min(
 *         [
 *             "min" => [
 *                 "name_last"  => 2,
 *                 "name_first" => 4,
 *             ],
 *             "message" => [
 *                 "name_last"  => "We don't like too short last names",
 *                 "name_first" => "We don't like too short first names",
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
class Min extends AbstractValidator
{
    use InfoTrait;

    /**
     * @var string|null
     */
    protected string | null $template = "Field :field must be at least :min characters long";

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
        if ($this->phpFunctionExists("mb_strlen")) {
            $length = mb_strlen((string)$value);
        } else {
            $length = strlen((string)$value);
        }

        $minimum = $this->getOption("min");

        if (is_array($minimum)) {
            $minimum = $minimum[$field];
        }

        $included = $this->getOption("included");

        if (is_array($included)) {
            $included = (bool)$included[$field];
        } else {
            $included = (bool)$included;
        }

        if ($included) {
            $failed = $length < $minimum;
        } else {
            $failed = $length <= $minimum;
        }

        if ($failed) {
            $replacePairs = [
                ":min" => $minimum,
            ];

            $validation->appendMessage(
                $this->messageFactory($validation, $field, $replacePairs)
            );

            return false;
        }

        return true;
    }
}
