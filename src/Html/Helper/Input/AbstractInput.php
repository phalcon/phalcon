<?php

/**
 * This file is part of the Phalcon Framework.
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Phalcon\Html\Helper\Input;

use Phalcon\Html\Helper\AbstractHelper;
use Phalcon\Html\Helper\Doctype;

use function array_merge;

/**
 * Class AbstractInput
 *
 * @package Phalcon\Html\Helper\Input
 *
 * @property array  $attributes
 * @property string $type
 * @property string $value
 */
abstract class AbstractInput extends AbstractHelper
{
    /**
     * @var array
     */
    protected array $attributes = [];
    /**
     * @var string
     */
    protected string $type = 'text';

    /**
     * @param string      $name
     * @param string|null $value
     * @param array       $attributes
     *
     * @return static
     */
    public function __invoke(
        string $name,
        string | null $value = null,
        array $attributes = []
    ): static {
        $this->attributes = [
            'type' => $this->type,
            'name' => $name,
        ];

        if (!isset($attributes['id'])) {
            $this->attributes['id'] = $name;
        }

        $this->setValue($value);

        $this->attributes = array_merge($this->attributes, $attributes);

        return $this;
    }

    /**
     * Returns the HTML for the input.
     *
     * @return string
     */
    public function __toString()
    {
        $closeTag = '';
        if ($this->doctype->getType() > Doctype::HTML5) {
            $closeTag = '/';
        }

        $output = $this->renderTag(
            'input',
            $this->attributes,
            $closeTag
        );

        $this->attributes = [];

        return $output;
    }

    /**
     * Sets the value of the element
     *
     * @param string|null $value
     *
     * @return AbstractInput
     */
    public function setValue(string | null $value = null): AbstractInput
    {
        if (null !== $value) {
            $this->attributes['value'] = $value;
        }

        return $this;
    }
}
