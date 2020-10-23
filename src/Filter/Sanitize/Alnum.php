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

use function preg_replace;

/**
 * Phalcon\Filter\Sanitize\Alnum
 *
 * Sanitizes a value to an alphanumeric value
 */
class Alnum
{
    /**
     * @param mixed $input The text to sanitize
     *
     * @return string|string[]|null
     */
    public function __invoke($input)
    {
        return preg_replace("/[^A-Za-z0-9]/", "", $input);
    }
}
