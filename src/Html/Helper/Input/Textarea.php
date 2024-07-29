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
 * Class Textarea
 *
 * @package Phalcon\Html\Helper\Input
 *
 * @property string $type
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
