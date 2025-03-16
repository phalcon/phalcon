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
 *
 * @package Phalcon\Html\Helper
 */
class Script extends AbstractSeries
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
            'renderFullElement',
            [
                $this->getTag(),
                '',
                $this->getAttributes($href, $attributes),
            ],
            $this->indent(),
        ];

        return $this;
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
            'type' => 'application/javascript',
        ];

        unset($attributes['src']);

        return array_merge($required, $attributes);
    }

    /**
     * @return string
     */
    protected function getTag(): string
    {
        return "script";
    }
}
