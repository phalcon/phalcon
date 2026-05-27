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

use Phalcon\Html\Helper\AbstractHelper;
use Phalcon\Html\Helper\Doctype;

use function array_merge;

/**
 * Class AbstractInput
 *
 * @property array  $attributes
 * @property string $type
 * @property string $value
 */
abstract class AbstractInput extends AbstractHelper
{
    /**
     * @var string
     */
    protected string $type = 'text';

    /**
     * @var array
     */
    protected array $attributes = [];

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

        if (!isset($attributes['id']) && !str_contains($name, '[')) {
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
    public function setValue(string | null $value = null): static
    {
        if (null !== $value) {
            $this->attributes['value'] = $value;
        }

        return $this;
    }
}
