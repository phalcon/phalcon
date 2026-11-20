<?php

/**
 * This file is part of the Phalcon Framework.
 *
 * (c) Phalcon Team <team@phalcon.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Phalcon\Filter\Validation\Validator;

use Phalcon\Contracts\Filter\FilterTypes;
use Phalcon\Filter\Validation;
use Phalcon\Filter\Validation\AbstractValidator;
use Phalcon\Messages\Message;
use Phalcon\Support\Traits\WeakTypesTrait;

use function implode;
use function in_array;

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
 *
 * @phpstan-import-type filter_validator_options from FilterTypes
 */
class ExclusionIn extends AbstractValidator
{
    use WeakTypesTrait;

    protected $template = "Field :field must not be a part of list: :domain";

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
        $value = self::weakCallMethod($validation, 'getValue', [$field]);
        if ($this->allowEmpty($field, $value)) {
            return true;
        }

        /**
         * A domain is an array with a list of valid values
         */
        $domain = $this->getDomainOption($field);

        /**
         * Check if the value is contained by the array
         *
         * @phpstan-var array<array-key, string> $domain
         */
        if (in_array($value, $domain, $this->getStrictOption($field))) {
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
