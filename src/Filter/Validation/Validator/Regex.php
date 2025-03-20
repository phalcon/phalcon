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

use function preg_match;

/**
 * Allows validate if the value of a field matches a regular expression
 *
 * ```php
 * use Phalcon\Filter\Validation;
 * use Phalcon\Filter\Validation\Validator\Regex as RegexValidator;
 *
 * $validator = new Validation();
 *
 * $validator->add(
 *     "created_at",
 *     new RegexValidator(
 *         [
 *             "pattern" => "/^[0-9]{4}[-\/](0[1-9]|1[12])[-\/](0[1-9]|[12][0-9]|3[01])$/",
 *             "message" => "The creation date is invalid",
 *         ]
 *     )
 * );
 *
 * $validator->add(
 *     [
 *         "created_at",
 *         "name",
 *     ],
 *     new RegexValidator(
 *         [
 *             "pattern" => [
 *                 "created_at" => "/^[0-9]{4}[-\/](0[1-9]|1[12])[-\/](0[1-9]|[12][0-9]|3[01])$/",
 *                 "name"       => "/^[a-z]$/",
 *             ],
 *             "message" => [
 *                 "created_at" => "The creation date is invalid",
 *                 "name"       => "The name is invalid",
 *             ]
 *         ]
 *     )
 * );
 * ```
 */
class Regex extends AbstractValidator
{
    /**
     * @var string|null
     */
    protected string | null $template = "Field :field does not match the required format";

    /**
     * Executes the validation
     */
    public function validate(Validation $validation, string $field): bool
    {
        $matches = [];
        $value   = $validation->getValue($field);
        if (true === $this->allowEmpty($field, $value)) {
            return true;
        }

        $pattern = $this->checkArray($this->getOption("pattern"), $field);

        /**
         * Since PHP8.1 $value can't be null.
         */
        $failed = true;
        if (null !== $value && preg_match($pattern, $value, $matches)) {
            $failed = $matches[0] !== $value;
        }

        if ($failed) {
            $validation->appendMessage(
                $this->messageFactory($validation, $field)
            );

            return false;
        }

        return true;
    }
}
