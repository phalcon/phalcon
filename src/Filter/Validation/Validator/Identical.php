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

use function is_array;

/**
 * Checks if a value is identical to other
 *
 * ```php
 * use Phalcon\Filter\Validation;
 * use Phalcon\Filter\Validation\Validator\Identical;
 *
 * $validator = new Validation();
 *
 * $validator->add(
 *     "terms",
 *     new Identical(
 *         [
 *             "accepted" => "yes",
 *             "message" => "Terms and conditions must be accepted",
 *         ]
 *     )
 * );
 *
 * $validator->add(
 *     [
 *         "terms",
 *         "anotherTerms",
 *     ],
 *     new Identical(
 *         [
 *             "accepted" => [
 *                 "terms"        => "yes",
 *                 "anotherTerms" => "yes",
 *             ],
 *             "message" => [
 *                 "terms"        => "Terms and conditions must be accepted",
 *                 "anotherTerms" => "Another terms  must be accepted",
 *             ],
 *         ]
 *     )
 * );
 * ```
 *
 * @phpstan-import-type filter_validator_options from FilterTypes
 */
class Identical extends AbstractValidator
{
    /**
     * @var string|null
     */
    protected string | null $template = "Field :field does not have the expected value";

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
        $accepted = false;
        $valid    = false;
        $value    = $validation->getValue($field);
        if ($this->allowEmpty($field, $value)) {
            return true;
        }

        if ($this->hasOption("accepted")) {
            $accepted = $this->getOption("accepted");
        } elseif ($this->hasOption("value")) {
            $accepted = $this->getOption("value");
        }

        if ($accepted) {
            if (is_array($accepted)) {
                $accepted = $accepted[$field];
            }

            $valid = $value == $accepted;
        }

        if (!$valid) {
            $validation->appendMessage(
                $this->messageFactory($validation, $field)
            );

            return false;
        }

        return true;
    }
}
