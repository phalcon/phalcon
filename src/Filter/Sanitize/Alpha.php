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

use Phalcon\Contracts\Filter\Sanitizer;

use function preg_replace;

/**
 * Sanitizes a value to an alpha value
 */
class Alpha implements Sanitizer
{
    /**
     * @param string[]|string $input The text to sanitize
     *
     * @return string|string[]|null
     */
    public function __invoke(array | string $input)
    {
        return preg_replace("/[^A-Za-z]/", "", $input);
    }
}
