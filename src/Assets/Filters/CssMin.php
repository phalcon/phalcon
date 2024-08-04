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
 * @link https://github.com/matthiasmullie/minify
 * @license https://github.com/matthiasmullie/minify/blob/master/LICENSE
 */

declare(strict_types=1);

namespace Phalcon\Assets\Filters;

use MatthiasMullie\Minify\CSS;
use Phalcon\Assets\FilterInterface;

/**
 * Minify the CSS - removes comments removes newlines and line feeds keeping
 * removes last semicolon from last property
 *
 * Class CssMin
 *
 * @package Phalcon\Assets\Filters
 */
class CssMin implements FilterInterface
{
    /**
     * Filters the content using CSSMIN
     * NOTE: This functionality is not currently available
     *
     * @param string $content
     *
     * @return string
     */
    public function filter(string $content): string
    {
        $minifier = new CSS();
        $minifier->add($content);

        return $minifier->minify();
    }
}
