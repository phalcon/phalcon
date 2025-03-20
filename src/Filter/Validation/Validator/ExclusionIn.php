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
use Phalcon\Filter\Validation\Exception as ValidationException;

use function in_array;
use function is_array;
use function is_bool;

/**
 * Check if a value is not included into a list of values
 *
 * ```php
 * use Phalcon\Filter\Validation;
 * use Phalcon\Filter\Validation\Validator\ExclusionIn;
 *
 * $validator = new Validation();
 *
 * $validator->add(
 *     "status",
 *     new ExclusionIn(
 *         [
 *             "message" => "The status must not be A or B",
 *             "domain"  => [
 *                 "A",
 *                 "B",
 *             ],
 *         ]
 *     )
 * );
 *
 * $validator->add(
 *     [
 *         "status",
 *         "type",
 *     ],
 *     new ExclusionIn(
 *         [
 *             "message" => [
 *                 "status" => "The status must not be A or B",
 *                 "type"   => "The type must not be 1 or "
 *             ],
 *             "domain" => [
 *                 "status" => [
 *                     "A",
 *                     "B",
 *                 ],
 *                 "type"   => [1, 2],
 *             ],
 *         ]
 *     )
 * );
 * ```
 */
class ExclusionIn extends AbstractValidator
{
    protected string | null $template = "Field :field must not be a part of list: :domain";

    /**
     * Executes the validation
     */
    public function validate(Validation $validation, string $field): bool
    {
        $value = $validation->getValue($field);
        if (true === $this->allowEmpty($field, $value)) {
            return true;
        }

        /**
         * A domain is an array with a list of valid values
         */
        $domain = $this->getOption("domain");
        if (
            is_array($domain) &&
            isset($domain[$field]) &&
            is_array($domain[$field])
        ) {
            $domain = $domain[$field];
        }

        if (!is_array($domain)) {
            throw new ValidationException("Option 'domain' must be an array");
        }

        $strict = false;
        if (true === $this->hasOption("strict")) {
            $strict = $this->checkArray($this->getOption("strict"), $field);

            if (!is_bool($strict)) {
                throw new ValidationException("Option 'strict' must be a bool");
            }
        }

        /**
         * Check if the value is contained by the array
         */
        if (true === $this->getConditional($value, $domain, $strict)) {
            $replacePairs = [
                ":domain" => implode(", ", $domain),
            ];

            $validation->appendMessage(
                $this->messageFactory($validation, $field, $replacePairs)
            );

            return false;
        }

        return true;
    }

    /**
     * Execute the conditional
     *
     * @param mixed $value
     * @param array $domain
     * @param bool  $strict
     *
     * @return bool
     */
    protected function getConditional(
        mixed $value,
        array $domain,
        bool $strict
    ): bool {
        return in_array($value, $domain, $strict);
    }
}
