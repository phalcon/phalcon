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

namespace Phalcon\Filter\Sanitize;

/**
 * Phalcon\Filter\Sanitize\IP
 *
 * Sanitizes a value to an ip address or CIDR range
 */
class Ip
{
    /**
     * @param string $input
     * @param int $filter
     * @return false|string
     */
    public function __invoke(string $input, int $filter = 0): string|false
    {
        $input = trim($input);

        if (strpos($input, "/") !== false) {
            [$ip, $mask] = explode("/", $input, 2);

            $filtered = filter_var($ip, FILTER_VALIDATE_IP, $filter);
            if ($filtered === false) {
                return false;
            }

            if (!ctype_digit($mask)) {
                return false;
            }

            $maxMask = strpos($filtered, ':') !== false ? 128 : 32;

            if ((int)$mask <= $maxMask) {
                return $filtered . "/" . $mask;
            }

            return false;
        }

        return filter_var($input, FILTER_VALIDATE_IP, $filter);
    }
}
