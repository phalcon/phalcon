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

namespace Phalcon\Tests\Fixtures\Traits;

use RuntimeException;

trait CookieTrait
{
    /**
     * Gets a value set with setcookie.
     *
     * A clean and transparent way to get a value set with setcookie() within
     * the same request
     *
     * @link  https://tools.ietf.org/html/rfc6265#section-4.1.1
     *
     * @param string $name
     *
     * @return string|array|null
     *
     * @throws RuntimeException
     */
    protected function getCookie($name)
    {
        $cookies = [];

        if (PHP_SAPI == 'cli') {
            if (!extension_loaded('xdebug')) {
                throw new RuntimeException(
                    'The xdebug extension is not loaded.'
                );
            }

            $headers = xdebug_get_headers();
        } else {
            $headers = headers_list();
        }

        foreach ($headers as $header) {
            if (strpos($header, 'Set-Cookie: ') === 0) {
                $value = str_replace(
                    '&',
                    urlencode('&'),
                    substr($header, 12)
                );

                parse_str(
                    current(
                        explode(
                            ';',
                            $value,
                            1
                        )
                    ),
                    $pair
                );

                $cookies = array_merge_recursive($cookies, $pair);
            }
        }

        return $cookies[$name] ?? null;
    }
}
