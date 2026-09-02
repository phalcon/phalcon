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
use Phalcon\Filter\Validation\AbstractValidatorComposite;
use Phalcon\Filter\Validation\Validator\StringLength\Max;
use Phalcon\Filter\Validation\Validator\StringLength\Min;
use Phalcon\Messages\Message;

/**
 * Validates that a string has the specified maximum and minimum constraints
 * The test is passed if for a string's length L, min<=L<=max, i.e. L must
 * be at least min, and at most max.
 * Since Phalcon v4.0 this validator works like a container
 *
 * The "includedMinimum" and "includedMaximum" options are true by
 * default. Set an option to false to exclude that boundary. The two
 * options are independent of each other. The "included" option sets
 * the two boundaries together and has precedence.
 *
 * ```php
 * use Phalcon\Filter\Validation;
 * use Phalcon\Filter\Validation\Validator\StringLength as StringLength;
 *
 * $validator = new Validation();
 *
 * $validation->add(
 *     "name_last",
 *     new StringLength(
 *         [
 *             "max"             => 50,
 *             "min"             => 2,
 *             "messageMaximum"  => "We don't like really long names",
 *             "messageMinimum"  => "We want more than just their initials",
 *             "includedMaximum" => true,
 *             "includedMinimum" => false,
 *         ]
 *     )
 * );
 *
 * $validation->add(
 *     [
 *         "name_last",
 *         "name_first",
 *     ],
 *     new StringLength(
 *         [
 *             "max" => [
 *                 "name_last"  => 50,
 *                 "name_first" => 40,
 *             ],
 *             "min" => [
 *                 "name_last"  => 2,
 *                 "name_first" => 4,
 *             ],
 *             "messageMaximum" => [
 *                 "name_last"  => "We don't like really long last names",
 *                 "name_first" => "We don't like really long first names",
 *             ],
 *             "messageMinimum" => [
 *                 "name_last"  => "We don't like too short last names",
 *                 "name_first" => "We don't like too short first names",
 *             ],
 *             "includedMaximum" => [
 *                 "name_last"  => false,
 *                 "name_first" => true,
 *             ],
 *             "includedMinimum" => [
 *                 "name_last"  => false,
 *                 "name_first" => true,
 *             ]
 *         ]
 *     )
 * );
 * ```
 *
 * @phpstan-import-type filter_validator_options from FilterTypes
 */
class StringLength extends AbstractValidatorComposite
{
    /**
     * Constructor
     *
     * @phpstan-param filter_validator_options $options
     */
    public function __construct(array $options = [])
    {
        $included = null;
        $message  = null;

        /**
         * The generic options apply to both validators. Read them before the
         * loop, because each branch removes them from the options
         */
        $hasIncluded = isset($options["included"]);
        $hasMessage  = isset($options["message"]);

        if ($hasIncluded) {
            $included = $options["included"];
        }

        if ($hasMessage) {
            $message = $options["message"];
        }

        // create individual validators
        foreach ($options as $key => $value) {
            if (strcasecmp($key, "min") === 0) {
                // get custom message
                $messageMinimum = $message;

                if (!$hasMessage && isset($options["messageMinimum"])) {
                    $messageMinimum = $options["messageMinimum"];
                }

                // get included option
                $includedMinimum = $included;

                if (!$hasIncluded && isset($options["includedMinimum"])) {
                    $includedMinimum = $options["includedMinimum"];
                }

                $validator = new Min(
                    [
                        "min"      => $value,
                        "message"  => $messageMinimum,
                        "included" => $includedMinimum,
                    ]
                );

                unset($options["min"]);
                unset($options["message"]);
                unset($options["messageMinimum"]);
                unset($options["included"]);
                unset($options["includedMinimum"]);
            } elseif (strcasecmp($key, "max") === 0) {
                // get custom message
                $messageMaximum = $message;

                if (!$hasMessage && isset($options["messageMaximum"])) {
                    $messageMaximum = $options["messageMaximum"];
                }

                // get included option
                $includedMaximum = $included;

                if (!$hasIncluded && isset($options["includedMaximum"])) {
                    $includedMaximum = $options["includedMaximum"];
                }

                $validator = new Max(
                    [
                        "max"      => $value,
                        "message"  => $messageMaximum,
                        "included" => $includedMaximum,
                    ]
                );

                unset($options["max"]);
                unset($options["message"]);
                unset($options["messageMaximum"]);
                unset($options["included"]);
                unset($options["includedMaximum"]);
            } else {
                continue;
            }

            $this->validators[] = $validator;
        }

        parent::__construct($options);
    }
}
