<?php

/**
 * This file is part of the Phalcon Framework.
 *
 * (c) Phalcon Team <team@phalcon.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * Implementation of this file has been influenced by AuraPHP
 * @link    https://github.com/auraphp/Aura.Html
 * @license https://github.com/auraphp/Aura.Html/blob/2.x/LICENSE
 */

declare(strict_types=1);

namespace Phalcon\Html\Helper;

use function array_merge;

/**
 * Class Script
 */
class Script extends AbstractSeries
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
                'renderFullElement',
                [
                    $this->getTag(),
                    '',
                    $this->getAttributes($href, $attributes),
                ],
                $this->indent(),
            ],
            $position
        );

        return $this;
    }

    /**
     * Begins capturing inline script content via output buffering. Pair
     * with `endInternal()` to close the buffer and append the captured
     * markup as a `<script>...</script>` block in the asset stack.
     */
    public function beginInternal(): void
    {
        ob_start();
    }

    /**
     * Closes an inline-script buffer opened by `beginInternal()` and adds
     * the captured content as a `<script>...</script>` entry. Any
     * attributes supplied are placed on the wrapping tag. The script body
     * is treated as raw HTML (it is JavaScript, not user-supplied text).
     *
     * @param array $attributes
     * @param int   $position
     *
     * @return static
     */
    public function endInternal(array $attributes = [], int $position = -1): static
    {
        $content = (string) ob_get_clean();

        $this->pushOrPlace(
            [
                'renderFullElement',
                [
                    $this->getTag(),
                    $content,
                    $attributes,
                    true,
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
        return 'script';
    }
}
