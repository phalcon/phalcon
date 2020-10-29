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

namespace Phalcon\Assets\Inline;

use Phalcon\Assets\Inline as InlineBase;

/**
 * Represents an inlined CSS
 */
class Css extends InlineBase
{
    /**
     * Phalcon\Assets\Inline\Css constructor
     *
     * Css constructor.
     *
     * @param string $content
     * @param bool   $filter
     * @param array  $attributes
     */
    public function __construct(
        string $content,
        bool $filter = true,
        array $attributes = []
    ) {
        if (true === empty($attributes)) {
            $attributes = [
                'type' => 'text/css'
            ];
        }

        parent::__construct('css', $content, $filter, $attributes);
    }
}
