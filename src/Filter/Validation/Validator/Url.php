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

use function filter_var;

use const FILTER_VALIDATE_URL;

/**
 * Checks if a value has a url format
 *
 * ```php
 * use Phalcon\Filter\Validation;
 * use Phalcon\Filter\Validation\Validator\Url as UrlValidator;
 *
 * $validator = new Validation();
 *
 * $validator->add(
 *     "url",
 *     new UrlValidator(
 *         [
 *             "message" => ":field must be a url",
 *         ]
 *     )
 * );
 *
 * $validator->add(
 *     [
 *         "url",
 *         "homepage",
 *     ],
 *     new UrlValidator(
 *         [
 *             "message" => [
 *                 "url"      => "url must be a url",
 *                 "homepage" => "homepage must be a url",
 *             ]
 *         ]
 *     )
 * );
 * ```
 */
class Url extends AbstractValidator
{
    /**
     * @var string|null
     */
    protected string | null $template = "Field :field must be a url";

    /**
     * Executes the validation
     */
    public function validate(Validation $validation, string $field): bool
    {
        $value = $validation->getValue($field);
        if (true === $this->allowEmpty($field, $value)) {
            return true;
        }

        if (isset($this->options["options"])) {
            $result = filter_var(
                $value,
                FILTER_VALIDATE_URL,
                $this->options["options"]
            );
        } else {
            $result = filter_var($value, FILTER_VALIDATE_URL);
        }

        if (!$result) {
            $validation->appendMessage(
                $this->messageFactory($validation, $field)
            );

            return false;
        }

        return true;
    }
}
