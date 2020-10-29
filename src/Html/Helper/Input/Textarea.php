<?php

/**
 * This file is part of the Phalcon Framework.
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Phalcon\Html\Helper\Input;

use Phalcon\Html\Exception;

/**
 * Class Textarea
 */
class Textarea extends AbstractInput
{
    /**
     * @var string
     */
    protected $type = 'textarea';

    /**
     * Returns the HTML for the input.
     *
     * @return string
     * @throws Exception
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
