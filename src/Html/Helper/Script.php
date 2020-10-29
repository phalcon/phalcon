<?php

/**
 * This file is part of the Phalcon Framework.
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Phalcon\Html\Helper;

use function array_merge;

/**
 * Class Script
 */
class Script extends Style
{
    /**
     * @return string
     */
    protected function getTag(): string
    {
        return 'script';
    }

    /**
     * Returns the necessary attributes
     *
     * @param string $src
     * @param array  $attributes
     *
     * @return array
     */
    protected function getAttributes(string $src, array $attributes): array
    {
        $required = [
            'src'  => $src,
            'type' => 'text/javascript',
        ];

        unset($attributes["src"]);

        return array_merge($required, $attributes);
    }
}
