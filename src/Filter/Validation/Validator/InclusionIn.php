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

use function in_array;

/**
 * Check if a value is included into a list of values
 *
 * ```php
 * use Phalcon\Filter\Validation;
 * use Phalcon\Filter\Validation\Validator\InclusionIn;
 *
 * $validator = new Validation();
 *
 * $validator->add(
 *     "status",
 *     new InclusionIn(
 *         [
 *             "message" => "The status must be A or B",
 *             "domain"  => ["A", "B"],
 *         ]
 *     )
 * );
 *
 * $validator->add(
 *     [
 *         "status",
 *         "type",
 *     ],
 *     new InclusionIn(
 *         [
 *             "message" => [
 *                 "status" => "The status must be A or B",
 *                 "type"   => "The status must be 1 or 2",
 *             ],
 *             "domain" => [
 *                 "status" => ["A", "B"],
 *                 "type"   => [1, 2],
 *             ]
 *         ]
 *     )
 * );
 * ```
 */
class InclusionIn extends ExclusionIn
{
    /**
     * @var string|null
     */
    protected string | null $template = "Field :field must be a part of list: :domain";

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
        return !in_array($value, $domain, $strict);
    }
}
