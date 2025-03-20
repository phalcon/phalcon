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

use const FILTER_FLAG_IPV4;
use const FILTER_FLAG_IPV6;
use const FILTER_FLAG_NO_PRIV_RANGE;
use const FILTER_FLAG_NO_RES_RANGE;
use const FILTER_VALIDATE_IP;

/**
 * Check for IP addresses
 *
 * ```php
 * use Phalcon\Filter\Validation\Validator\Ip as IpValidator;
 *
 * $validator->add(
 *     "ip_address",
 *     new IpValidator(
 *         [
 *             "message"       => ":field must contain only ip addresses",
 *             "version"       => IP::VERSION_4 | IP::VERSION_6, // v6 and v4. The same if not specified
 *             "allowReserved" => false,   // False if not specified. Ignored for v6
 *             "allowPrivate"  => false,   // False if not specified
 *             "allowEmpty"    => false,
 *         ]
 *     )
 * );
 *
 * $validator->add(
 *     [
 *         "source_address",
 *         "destination_address",
 *     ],
 *     new IpValidator(
 *         [
 *             "message" => [
 *                 "source_address"      => "source_address must be a valid IP address",
 *                 "destination_address" => "destination_address must be a valid IP address",
 *             ],
 *             "version" => [
 *                  "source_address"      => Ip::VERSION_4 | IP::VERSION_6,
 *                  "destination_address" => Ip::VERSION_4,
 *             ],
 *             "allowReserved" => [
 *                  "source_address"      => false,
 *                  "destination_address" => true,
 *             ],
 *             "allowPrivate" => [
 *                  "source_address"      => false,
 *                  "destination_address" => true,
 *             ],
 *             "allowEmpty" => [
 *                  "source_address"      => false,
 *                  "destination_address" => true,
 *             ],
 *         ]
 *     )
 * );
 * ```
 */
class Ip extends AbstractValidator
{
    public const VERSION_4 = FILTER_FLAG_IPV4;
    public const VERSION_6 = FILTER_FLAG_IPV6;

    /**
     * @var string|null
     */
    protected string | null $template = "Field :field must be a valid IP address";

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
        $value = $validation->getValue($field);
        if (true === $this->allowEmpty($field, $value)) {
            return true;
        }

        $version       = $this->checkArray(
            $this->getOption("version", FILTER_FLAG_IPV4 | FILTER_FLAG_IPV6),
            $field
        );
        $allowPrivate  = $this->checkArray(
            $this->getOption("allowPrivate") ? 0 : FILTER_FLAG_NO_PRIV_RANGE,
            $field
        );
        $allowReserved = $this->checkArray(
            $this->getOption("allowReserved") ? 0 : FILTER_FLAG_NO_RES_RANGE,
            $field
        );

        $options = [
            "options" => [
                "default" => false,
            ],
            "flags"   => $version | $allowPrivate | $allowReserved,
        ];

        if (!filter_var($value, FILTER_VALIDATE_IP, $options)) {
            $validation->appendMessage(
                $this->messageFactory($validation, $field)
            );

            return false;
        }

        return true;
    }
}
