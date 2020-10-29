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
 * Class Link
 */
class Link extends AbstractSeries
{
    /**
     * Add an element to the list
     *
     * @param string $rel
     * @param string $href
     *
     * @return Link
     */
    public function add(string $rel, string $href): Link
    {
        $attributes = [
            'rel'  => $rel,
            'href' => $href,
        ];

        $this->store[] = [
            "renderTag",
            [
                $this->getTag(),
                $attributes,
                "/",
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
