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

use Phalcon\Filter\Validation\AbstractValidatorComposite;
use Phalcon\Filter\Validation\Validator\StringLength\Max;
use Phalcon\Filter\Validation\Validator\StringLength\Min;

/**
 * Validates that a string has the specified maximum and minimum constraints
 * The test is passed if for a string's length L, min<=L<=max, i.e. L must
 * be at least min, and at most max.
 * Since Phalcon v4.0 this validator works like a container
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
 */
class StringLength extends AbstractValidatorComposite
{
    /**
     * Constructor
     *
     * @param array $options = [
     *                       'min'             => 100,
     *                       'message'         => '',
     *                       'messageMinimum'  => '',
     *                       'included'        => '',
     *                       'includedMinimum' => false,
     *                       'max'             => 1000,
     *                       'messageMaximum'  => '',
     *                       'includedMaximum' => false,
     *                       ]
     */
    public function __construct(array $options = [])
    {
        $this
            ->processValidator(
                $options,
                Min::class,
                "min",
                "messageMinimum",
                "includedMinimum"
            )
            ->processValidator(
                $options,
                Max::class,
                "max",
                "messageMaximum",
                "includedMaximum"
            )
        ;

        unset(
            $options["min"],
            $options["message"],
            $options["messageMinimum"],
            $options["included"],
            $options["includedMinimum"],
            $options["max"],
            $options["messageMaximum"],
            $options["includedMaximum"]
        );

        parent::__construct($options);
    }

    /**
     * @param array  $options
     * @param string $class
     * @param string $key
     * @param string $messageKey
     * @param string $includedKey
     *
     * @return StringLength
     */
    private function processValidator(
        array $options,
        string $class,
        string $key,
        string $messageKey,
        string $includedKey
    ): StringLength {
        if (isset($options[$key])) {
            $message  = null;
            $included = false;
            // get custom message
            if (isset($options["message"])) {
                $message = $options["message"];
            } elseif (isset($options[$messageKey])) {
                $message = $options[$messageKey];
            }

            // get included option
            if (isset($options["included"])) {
                $included = $options["included"];
            } elseif (isset($options[$includedKey])) {
                $included = $options[$includedKey];
            }

            $this->validators[] = new $class(
                [
                    $key       => $options[$key],
                    "message"  => $message,
                    "included" => $included,
                ]
            );
        }

        return $this;
    }
}
