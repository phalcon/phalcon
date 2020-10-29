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
    protected $type = "text";

    /**
     * @var array
     */
    protected $attributes = [];

    /**
     * @param string      $name
     * @param string|null $value
     * @param array       $attributes
     *
     * @return AbstractInput
     */
    public function __invoke(
        string $name,
        string $value = null,
        array $attributes = []
    ): AbstractInput {
        $this->attributes = [
            "type" => $this->type,
            "name" => $name,
        ];

        if (!isset($attributes["id"])) {
            $this->attributes["id"] = $name;
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
        $attributes       = $this->attributes;
        $this->attributes = [];

        return $this->renderTag(
            "input",
            $attributes,
            "/"
        );
    }

    /**
     * Sets the value of the element
     *
     * @param string|null $value
     *
     * @return AbstractInput
     */
    public function setValue(string $value = null): AbstractInput
    {
        if (null !== $value) {
            $this->attributes["value"] = $value;
        }

        return $this;
    }
}
