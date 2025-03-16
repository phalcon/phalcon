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
     * @return static
     */
    public function add(string $href, array $attributes = []): static
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
