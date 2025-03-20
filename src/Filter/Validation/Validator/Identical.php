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
 */
class Identical extends AbstractValidator
{
    /**
     * @var string|null
     */
    protected string | null $template = "Field :field does not have the expected value";

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
        $valid    = false;
        $accepted = false;
        $value    = $validation->getValue($field);
        if (true === $this->allowEmpty($field, $value)) {
            return true;
        }

        if (true === $this->hasOption("accepted")) {
            $accepted = $this->getOption("accepted");
        } elseif ($this->hasOption("value")) {
            $accepted = $this->getOption("value");
        }

        if ($accepted) {
            $accepted = $this->checkArray($accepted, $field);
            $valid    = $value === $accepted;
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
