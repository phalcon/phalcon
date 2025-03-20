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
 * Check for a valid numeric value
 *
 * ```php
 * use Phalcon\Filter\Validation;
 * use Phalcon\Filter\Validation\Validator\Numericality;
 *
 * $validator = new Validation();
 *
 * $validator->add(
 *     "price",
 *     new Numericality(
 *         [
 *             "message" => ":field is not numeric",
 *         ]
 *     )
 * );
 *
 * $validator->add(
 *     [
 *         "price",
 *         "amount",
 *     ],
 *     new Numericality(
 *         [
 *             "message" => [
 *                 "price"  => "price is not numeric",
 *                 "amount" => "amount is not numeric",
 *             ]
 *         ]
 *     )
 * );
 * ```
 */
class Numericality extends AbstractValidator
{
    /**
     * @var string|null
     */
    protected string | null $template = "Field :field does not have a valid numeric format";

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
        // Dump spaces in the string if we have any
        $value = $validation->getValue($field);
        $value = (string)$value;

        if (true === $this->allowEmpty($field, $value)) {
            return true;
        }

        // Dump spaces in the string if we have any
//        $value   = str_replace(" ", "", $value);
        $pattern = "/((^[-]?[0-9,]+(\\.\d+)?$)|(^[-]?[0-9.]+(,\d+)?$))/";

        if (!preg_match($pattern, $value)) {
            $validation->appendMessage(
                $this->messageFactory($validation, $field)
            );

            return false;
        }

        return true;
    }
}
