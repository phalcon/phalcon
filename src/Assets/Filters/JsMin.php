<?php

/**
 * This file is part of the Phalcon Framework.
 *
 * (c) Phalcon Team <team@phalcon.io>
 *
 * For the full copyright and license information, please view the LICENSE.txt
 * file that was distributed with this source code.
 *
 * Minifying logic supported by Minify by Mathias Mullie
 * @link    https://github.com/matthiasmullie/minify
 * @license https://github.com/matthiasmullie/minify/blob/master/LICENSE
 */

declare(strict_types=1);

namespace Phalcon\Assets\Filters;

use MatthiasMullie\Minify\JS;
use Phalcon\Assets\FilterInterface;

/**
 * Deletes the characters which are insignificant to JavaScript. Comments will
 * be removed. Tabs will be replaced with spaces. Carriage returns will be
 * replaced with linefeeds. Most spaces and linefeeds will be removed.
 */
class JsMin implements FilterInterface
{
    /**
     * Filters the content using JSMIN
     * NOTE: This functionality is not currently available
     */
    public function filter(string $content): string
    {
        $minifier = new JS();
        $minifier->add($content);

        return $minifier->minify();
    }
}
