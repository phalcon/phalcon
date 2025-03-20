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

use Phalcon\Di\Exception as DiException;
use Phalcon\Filter\Validation;
use Phalcon\Filter\Validation\AbstractValidator;
use Phalcon\Filter\Validation\Exception;
use Phalcon\Traits\Php\InfoTrait;

use function strcmp;

/**
 * Checks that two values have the same value
 *
 * ```php
 * use Phalcon\Filter\Validation;
 * use Phalcon\Filter\Validation\Validator\Confirmation;
 *
 * $validator = new Validation();
 *
 * $validator->add(
 *     "password",
 *     new Confirmation(
 *         [
 *             "message" => "Password doesn't match confirmation",
 *             "with"    => "confirmPassword",
 *         ]
 *     )
 * );
 *
 * $validator->add(
 *     [
 *         "password",
 *         "email",
 *     ],
 *     new Confirmation(
 *         [
 *             "message" => [
 *                 "password" => "Password doesn't match confirmation",
 *                 "email"    => "Email doesn't match confirmation",
 *             ],
 *             "with" => [
 *                 "password" => "confirmPassword",
 *                 "email"    => "confirmEmail",
 *             ],
 *         ]
 *     )
 * );
 * ```
 */
class Confirmation extends AbstractValidator
{
    use InfoTrait;

    /**
     * @var string|null
     */
    protected string | null $template = "Field :field must be the same as :with";

    /**
     * Executes the validation
     *
     * @param Validation $validation
     * @param string     $field
     *
     * @return bool
     * @throws Exception
     * @throws DiException
     */
    public function validate(Validation $validation, string $field): bool
    {
        $fieldWith = $this->checkArray($this->getOption("with"), $field);
        $value     = $validation->getValue($field);
        $valueWith = $validation->getValue($fieldWith);

        if (true !== $this->compare((string)$value, (string)$valueWith)) {
            $labelWith = $this->checkArray(
                $this->getOption("labelWith"),
                $field
            );

            if (empty($labelWith)) {
                $labelWith = $validation->getLabel($fieldWith);
            }

            $replacePairs = [
                ":with" => $labelWith,
            ];

            $validation->appendMessage(
                $this->messageFactory($validation, $field, $replacePairs)
            );

            return false;
        }

        return true;
    }

    /**
     * Compare strings
     *
     * @param string $source
     * @param string $target
     *
     * @return bool
     */
    final protected function compare(string $source, string $target): bool
    {
        if (true === $this->getOption("ignoreCase", false)) {
            $source = mb_strtolower($source, "utf-8");
            $target = mb_strtolower($target, "utf-8");
        }

        return 0 === strcmp($source, $target);
    }
}
