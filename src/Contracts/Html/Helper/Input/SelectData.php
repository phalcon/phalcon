<?php

/**
 * This file is part of the Phalcon Framework.
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Phalcon\Contracts\Html\Helper\Input;

use Phalcon\Contracts\Html\HtmlTypes;

/**
 * Interface for SELECT option data providers.
 *
 * Return format: [value => label] for flat options;
 * [groupLabel => [value => label, ...]] for optgroups.
 *
 * @phpstan-import-type html_select_attributes from HtmlTypes
 * @phpstan-import-type html_select_options from HtmlTypes
 */
interface SelectData
{
    /**
     * Returns the per-option attribute map.
     *
     * Format: [optionValue => [attrName => stringValue, ...]].
     * Implementations must return resolved string values; no escaping,
     * ordering, or rendering is performed here.
     *
     * @phpstan-return html_select_attributes
     */
    public function getAttributes(): array;

    /**
     * @phpstan-return html_select_options
     */
    public function getOptions(): array;
}
