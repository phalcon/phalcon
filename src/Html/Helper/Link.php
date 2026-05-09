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
 * Link class producing "link" elements
 */
class Link extends Style
{
    /**
     * Add an element to the list
     *
     * @param string $href
     * @param array  $attributes
     * @param int    $position
     *
     * @return static
     */
    public function add(string $href, array $attributes = [], int $position = -1): static
    {
        $this->pushOrPlace(
            [
                'renderTag',
                [
                    $this->getTag(),
                    $this->getAttributes($href, $attributes),
                    '/',
                ],
                $this->indent(),
            ],
            $position
        );

        return $this;
    }

    /**
     * Returns the necessary attributes
     *
     * @param string                $href
     * @param array<string, string> $attributes
     *
     * @return array<string, string>
     */
    protected function getAttributes(string $href, array $attributes): array
    {
        $required = [
            'href' => $href,
        ];

        unset($attributes['href']);

        return array_merge($required, $attributes);
    }

    /**
     * @return string
     */
    protected function getTag(): string
    {
        return 'link';
    }
}
