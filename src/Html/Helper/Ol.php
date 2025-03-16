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
 * Class Ol
 *
 * @package Phalcon\Html\Helper
 */
class Ol extends AbstractList
{
    /**
     * Add an element to the list
     *
     * @param string $text
     * @param array  $attributes
     * @param bool   $raw
     *
     * @return static
     */
    public function add(
        string $text,
        array $attributes = [],
        bool $raw = false
    ): static {
        $this->store[] = [
            'renderFullElement',
            [
                $this->elementTag,
                $text,
                $attributes,
                $raw,
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
        return 'ol';
    }
}
