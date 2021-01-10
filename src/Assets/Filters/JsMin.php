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

namespace Phiz\Assets\Filters;

use Phiz\Assets\FilterInterface;

/**
 * Deletes the characters which are insignificant to JavaScript. Comments will
 * be removed. Tabs will be replaced with spaces. Carriage returns will be
 * replaced with linefeeds. Most spaces and linefeeds will be removed.
 *
 * Class JsMin
 *
 * @package Phiz\Assets\Filters
 */
class JsMin implements FilterInterface
{
    /**
     * Filters the content using JSMIN
     * NOTE: This functionality is not currently available
     */
    public function filter(string $content): string
    {
        return $content;
    }
}
