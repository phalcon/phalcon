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
use Phalcon\Filter\Validation\Exception;
use Phalcon\Filter\Validation\Exceptions\InvalidDomainOption;
use Phalcon\Filter\Validation\Exceptions\InvalidStrictOption;
use Phalcon\Messages\Message;

use function implode;
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
    /**
     * @var string|null
     */
    protected string | null $template = "Field :field must not be a part of list: :domain";

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

        /**
         * A domain is an array with a list of valid values
         */
        $domain = $this->getOption("domain");

        if (isset($domain[$field])) {
            $fieldDomain = $domain[$field];
            if (is_array($fieldDomain)) {
                $domain = $fieldDomain;
            }
        }

        if (!is_array($domain)) {
            throw new InvalidDomainOption();
        }

        $strict = false;

        if ($this->hasOption("strict")) {
            $strict = $this->getOption("strict");

            if (is_array($strict)) {
                $strict = $strict[$field];
            }

            if (!is_bool($strict)) {
                throw new InvalidStrictOption();
            }
        }

        /**
         * Check if the value is contained by the array
         */
        if (in_array($value, $domain, $strict)) {
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
}
