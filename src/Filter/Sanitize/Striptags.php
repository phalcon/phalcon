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

use function strip_tags;

/**
 * Phalcon\Filter\Sanitize\Striptags
 *
 * Sanitizes a value striptags
 */
class Striptags
{
    /**
     * @param string $input The text to sanitize
     *
     * @return string
     */
    public function __invoke(string $input)
    {
        return strip_tags($input);
    }
}
