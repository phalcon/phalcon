<?php

/**
 * This file is part of the Phalcon Framework.
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Phalcon\Html\Helper;

/**
 * Class Close
 *
 * @package Phalcon\Html\Helper
 */
class Close extends AbstractHelper
{
    /**
     * Produce a `</...>` tag.
     *
     * @param string $tag
     * @param bool   $raw
     *
     * @return string
     */
    public function __invoke(string $tag, bool $raw = false): string
    {
        return $this->close($tag, $raw);
    }
}
