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
 * Class Style
 *
 * @package Phalcon\Html\Helper
 */
class Style extends AbstractSeries
{
    /**
     * @var bool
     */
    private bool $isStyle = false;

    /**
     * Add an element to the list
     *
     * @param string $url
     * @param array  $attributes
     *
     * @return static
     */
    public function add(string $url, array $attributes = []): static
    {
        $this->store[] = [
            "renderTag",
            [
                $this->getTag(),
                $this->getAttributes($url, $attributes),
                "/",
            ],
            $this->indent(),
        ];

        return $this;
    }

    /**
     * Sets if this is a style or link tag
     *
     * @param bool $flag
     *
     * @return $this
     */
    public function setStyle(bool $flag): Style
    {
        $this->isStyle = $flag;

        return $this;
    }

    /**
     * Returns the necessary attributes
     *
     * @param string $url
     * @param array  $attributes
     *
     * @return array
     */
    protected function getAttributes(string $url, array $attributes): array
    {
        $required = [
            "rel"   => "stylesheet",
            "href"  => $url,
            "type"  => "text/css",
            "media" => "screen",
        ];

        if (true === $this->isStyle) {
            unset($required["rel"]);
        }

        unset($attributes["href"]);

        return array_merge($required, $attributes);
    }

    /**
     * @return string
     */
    protected function getTag(): string
    {
        return true === $this->isStyle ? "style" : "link";
    }
}
