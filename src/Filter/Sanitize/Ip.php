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
        $protocol = $this->getIpAddressProtocolVersion($input);
        if ($protocol === false) {
            return false;
        }

        // CIDR notation (e.g., 192.168.1.0/24)
        if (strpos($input, "/") !== false) {
            $parts = explode("/", $input, 2);
            $ip    = $parts[0];
            $mask  = $parts[1];

            // Try IPv4 validation
            if ($protocol === 4) {
                $filtered = filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4 | $filter);
                if ($filtered) {
                    if (is_numeric($mask) && $mask >= 0 && $mask <= 32) {
                        return $filtered . "/" . $mask;
                    }
                }
            }

            // Try IPv6 validation
            if ($protocol === 6) {
                $filtered = filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6 | $filter);
                if ($filtered) {
                    if (is_numeric($mask) && $mask >= 0 && $mask <= 128) {
                        return $filtered . "/" . $mask;
                    }
                }
            }
        } else {
            // Single IP
            if ($protocol === 4) {
                return filter_var($input, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4 | $filter);
            }

            if ($protocol === 6) {
                return filter_var($input, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6 | $filter);
            }
        }

        // return false if nothing filtered.
        return false;
    }

    /**
     * Return the IP address protocol version
     *
     * @param string $input
     * @return int|false
     */
    private function getIpAddressProtocolVersion(string $input): int | false
    {
        $ip = $input;
        if (strpos($ip, "/") !== false) {
            $parts   = explode("/", $ip, 2);
            $ip      = $parts[0];
        }

        if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4) !== false) {
            return 4;
        }

        if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6) !== false) {
            return 6;
        }

        return false;
    }
}
