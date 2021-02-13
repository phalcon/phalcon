<?php

/**
 * This file is part of the Phalcon Framework.
 *
 * (c) Phalcon Team <team@phalcon.io>
 *
 * For the full copyright and license information, please view the LICENSE.txt
 * file that was distributed with this source code.
 */

namespace Phalcon\Forms\Element;

use Phalcon\Tag\Select as SelectTag;

/**
 * Phalcon\Forms\Element\Select
 *
 * Component SELECT (choice) for forms
 */
class Select extends AbstractElement
{
    protected $optionsValues;

    /**
     * Phalcon\Forms\Element constructor
     *
     * @param object|array options
     * @param array        attributes
     */
    public function __construct(string $name, $options = null, $attributes = null)
    {
        $this->optionsValues = $options;

        parent::__construct($name, $attributes);
    }

    /**
     * Adds an option to the current options
     *
     * @param array|string option
     */
    public function addOption($option):ElementInterface
    {
        if (gettype($option) == "array") {
            foreach($option as $key => $value) {
                $this->optionsValues[$key] = $value;
            }
        } else {
            $this->optionsValues[] = $option;
        }

        return $this;
    }

    /**
     * Returns the choices' options
     *
     * @return array|object
     */
    public function getOptions()
    {
        return $this->optionsValues;
    }

    /**
     * Renders the element widget returning HTML
     */
    public function render(array $attributes = []):string
    {
        /**
         * Merged passed attributes with previously defined ones
         */
        return SelectTag::selectField(
            $this->prepareAttributes($attributes),
            $this->optionsValues
        );
    }

    /**
     * Set the choice's options
     *
     * @param array|object options
     */
    public function setOptions($options):ElementInterface
    {
        $this->optionsValues = $options;

        return $this;
    }
}
