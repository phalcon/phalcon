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

namespace Phalcon\Support\Helper\Str;

class Interpolate
{
    /**
     * Interpolates context values into the message placeholders
     *
     * @see http://www.php-fig.org/psr/psr-3/ Section 1.2 Message
     *
     * @param string $message
     * @param array  $context
     */
    public function __invoke(
        string $message,
        array $context = [],
        string $leftToken = "%",
        string $rightToken = "%"
    ): string {
        if (true !== empty($context)) {
            $replace = [];

            foreach ($context as $key => $value) {
                $replace[$leftToken . $key . $rightToken] = $value;
            }

            return strtr($message, $replace);
        }

        return $message;
    }
}
