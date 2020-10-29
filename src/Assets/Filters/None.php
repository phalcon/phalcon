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

namespace Phalcon\Assets\Filters;

use Phalcon\Assets\FilterInterface;

/**
 * Returns the content without make any modification to the original source
 *
 * Class None
 *
 * @package Phalcon\Assets\Filters
 */
class None implements FilterInterface
{
    /**
     * Returns the content as is
     *
     * @param string $content
     *
     * @return string
     */
    public function filter(string $content): string
    {
        return $content;
    }
}
