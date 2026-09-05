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

namespace Phalcon\Html\Helper;

use Phalcon\Contracts\Html\HtmlTypes;

/**
 * Class Form
 *
 * @phpstan-import-type html_attributes from HtmlTypes
 */
class Form extends AbstractHelper
{
    /**
     * Produce a `<form>` tag.
     *
     * @phpstan-param html_attributes $attributes
     */
    public function __invoke(array $attributes = []): string
    {
        $overrides = [
            'method'  => 'post',
            'enctype' => 'multipart/form-data',
        ];

        $overrides = $this->orderAttributes($overrides, $attributes);

        return $this->renderElement('form', $overrides);
    }
}
