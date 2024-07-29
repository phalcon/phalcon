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
 * Class Label
 *
 * @package Phalcon\Html\Helper
 */
class Label extends AbstractHelper
{
    /**
     * Produce a `<label>` tag.
     *
     * @param string $label
     * @param array  $attributes
     * @param bool   $raw
     *
     * @return string
     */
    public function __invoke(
        string $label,
        array $attributes = [],
        bool $raw = false
    ): string {
        return $this->renderFullElement('label', $label, $attributes, $raw);
    }
}
