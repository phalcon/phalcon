<?php

/**
 * This file is part of the Phalcon Framework.
 *
 * (c) Phalcon Team <team@phalcon.io>
 *
 * For the full copyright and license information, please view the LICENSE.txt
 * file that was distributed with this source code.
 *
 * Implementation of this file has been influenced by AuraPHP
 * @link    https://github.com/auraphp/Aura.Html
 * @license https://github.com/auraphp/Aura.Html/blob/2.x/LICENSE
 */

declare(strict_types=1);

namespace Phalcon\Html\Helper\Input\Select;

use Phalcon\Contracts\Html\Helper\Input\SelectData;
use Phalcon\Contracts\Html\HtmlTypes;

/**
 * Wraps a plain PHP array as a SELECT data provider.
 *
 * Keys are option values; string values are labels;
 * array values define optgroups.
 *
 * @phpstan-import-type html_select_attributes from HtmlTypes
 * @phpstan-import-type html_select_options from HtmlTypes
 */
class ArrayData implements SelectData
{
    /**
     * @phpstan-param html_select_options    $data
     * @phpstan-param html_select_attributes $attributes
     */
    public function __construct(
        protected array $data = [],
        protected array $attributes = []
    ) {
    }

    /**
     * @phpstan-return html_select_attributes
     */
    public function getAttributes(): array
    {
        return $this->attributes;
    }

    /**
     * @phpstan-return html_select_options
     */
    public function getOptions(): array
    {
        return $this->data;
    }
}
