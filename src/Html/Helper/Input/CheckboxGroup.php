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
