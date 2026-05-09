<?php

/**
 * This file is part of the Phalcon Framework.
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Phalcon\Html\Helper\Input;

/**
 * Renders a group of `<input type="radio">` elements from an options array.
 *
 * The $checked parameter should be a single scalar value matching the selected
 * option's value attribute.
 */
class RadioGroup extends AbstractGroup
{
    /**
     * @var string
     */
    protected string $type = 'radio';

    /**
     * Returns true when $value loosely equals the checked scalar.
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

        return (string) $this->checked === $value;
    }
}
