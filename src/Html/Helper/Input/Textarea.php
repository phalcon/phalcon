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

/**
 * Class Textarea
 */
class Textarea extends AbstractInput
{
    /**
     * @var string
     */
    protected string $type = 'textarea';

    /**
     * Returns the HTML for the input.
     *
     * @return string
     */
    public function __toString()
    {
        $attributes       = $this->attributes;
        $this->attributes = [];
        $value            = $attributes['value'] ?? '';

        unset($attributes['type']);
        unset($attributes['value']);

        return $this->renderFullElement(
            $this->type,
            $value,
            $attributes
        );
    }
}
