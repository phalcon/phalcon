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
 * Class Link
 *
 * @package Phalcon\Html\Helper
 */
class Link extends Style
{
    /**
     * Add an element to the list
     *
     * @param string $href
     * @param array  $attributes
     *
     * @return Link
     */
    public function add(string $href, array $attributes = []): Link
    {
        $this->store[] = [
            'renderTag',
            [
                $this->getTag(),
                $this->getAttributes($href, $attributes),
                '/',
            ],
            $this->indent(),
        ];

        return $this;
    }

    /**
     * @return string
     */
    protected function getTag(): string
    {
        return 'link';
    }
}
