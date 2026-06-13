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

namespace Phalcon\Contracts\Assets;

/**
 * Canonical contract for Phalcon\Assets filters (Cssmin, Jsmin, None, and
 * custom user filters).
 */
interface Filter
{
    /**
     * Filters the content returning a string with the filtered content
     *
     * @param string $content
     *
     * @return string
     */
    public function filter(string $content): string;
}
