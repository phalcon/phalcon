<?php

/**
 * This file is part of the Phalcon Framework.
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Phalcon\Html\Helper\Input;

use function in_array;
use function is_array;

/**
 * Renders a group of `<input type="checkbox">` elements from an options array.
 *
 * The $checked parameter should be an array of selected values, or a single
 * scalar value (treated as a one-element array).
 */
class CheckboxGroup extends AbstractGroup
{
    /**
     * @var string
     */
    protected string $type = 'checkbox';

    /**
     * Returns true when $value appears in the checked list.
     *
     * @param string $value
     *
     * @return bool
     */
    protected function isChecked(string $value): bool
    {
        if (null === $this->checked) {
            return false;
        }

        $selected = is_array($this->checked) ? $this->checked : [$this->checked];

        return in_array($value, $selected, false);
    }
}
