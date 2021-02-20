<?php

/**
 * This file is part of the Phalcon Framework.
 *
 * (c) Phalcon Team <team@phalcon.io>
 *
 * For the full copyright and license information, please view the LICENSE.txt
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Phalcon\Forms\Element;

use Phalcon\Html\Tag\Input\Checkbox;
use Phalcon\Html\Escaper;

/**
 * Phalcon\Forms\Element\Check
 *
 * Component INPUT[type=check] for forms
 */
class Check extends AbstractElement
{
    /**
     * Renders the element widget returning HTML
     */
    public function render(array $attributes = []): string
    {
        $escaper = new Escaper();
        $helper  = new Checkbox($escaper);

        var_dump($attributes);
        $attributes = $this->prepareAttributes($attributes, true);
        var_dump($attributes);
        return $helper($attributes['name'], $attributes['value'], $attributes['attributes']);
    }
}
