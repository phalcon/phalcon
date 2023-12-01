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
 * Class Anchor
 *
 * @package Phalcon\Html\Helper
 */
class Anchor extends AbstractHelper
{
    /**
     * Produce a <a> tag
     *
     * @param string $href
     * @param string $text
     * @param array  $attributes
     * @param bool   $raw
     *
     * @return string
     */
    public function __invoke(
        string $href,
        string $text,
        array $attributes = [],
        bool $raw = false
    ): string {
        $overrides = $this->processAttributes($href, $attributes);

        return $this->renderFullElement('a', $text, $overrides, $raw);
    }

    /**
     * @param string $href
     * @param array  $attributes
     *
     * @return array
     */
    protected function processAttributes(string $href, array $attributes): array
    {
        $overrides = ['href' => $href];

        /**
         * Avoid duplicate 'href' and ignore it if it is passed in the attributes
         */
        unset($attributes['href']);

        return array_merge($overrides, $attributes);
    }
}
