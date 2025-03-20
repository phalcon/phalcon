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
use Phalcon\Traits\Php\InfoTrait;

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
     * Executes the validation
     *
     * @param Validation $validation
     * @param string     $field
     *
     * @return bool
     * @throws Exception
     */
    public function validate(Validation $validation, string $field): bool
    {
        $value = $validation->getValue($field);
        if (true === $this->allowEmpty($field, $value)) {
            return true;
        }

        $length      = mb_strlen((string)$value);
        $optionField = $this->getOptionField();
        $optionValue = $this->checkArray($this->getOption($optionField), $field);
        $included    = (bool)$this->checkArray(
            $this->getOption("included", false),
            $field
        );

        if (true === $this->getConditional($length, $optionValue, $included)) {
            $replacePairs = [
                ":" . $optionField => $optionValue,
            ];

            $validation->appendMessage(
                $this->messageFactory($validation, $field, $replacePairs)
            );

            return false;
        }

        return true;
    }

    /**
     * Executes the conditional
     *
     * @param int  $source
     * @param int  $target
     * @param bool $included
     *
     * @return bool
     */
    protected function getConditional(
        int $source,
        int $target,
        bool $included = false
    ): bool {
        if (true === $included) {
            return $source <= $target;
        }

        return $source < $target;
    }

    /**
     * @return string
     */
    protected function getOptionField(): string
    {
        return "min";
    }
}
