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

use Phalcon\Tag\Exception as TagException;
use Phalcon\Tag\Select as SelectTag;

use function is_array;

/**
 * Component SELECT (choice) for forms
 */
class Select extends AbstractElement
{
    /**
     * @var object|array|null
     */
    protected object | array | null $optionsValues = null;

    /**
     * Constructor
     *
     * @param string            $name
     * @param object|array|null $options
     * @param array             $attributes
     */
    public function __construct(
        string $name,
        object | array | null $options = null,
        array $attributes = []
    ) {
        $this->optionsValues = $options;

        parent::__construct($name, $attributes);
    }

    /**
     * Adds an option to the current options
     *
     * @param array|string $option
     *
     * @return ElementInterface
     */
    public function addOption(array | string $option): ElementInterface
    {
        if (is_array($option)) {
            foreach ($option as $key => $value) {
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
    public function getOptions(): array | object
    {
        return $this->optionsValues;
    }

    /**
     * Renders the element widget returning HTML
     *
     * @param array $attributes
     *
     * @return string
     * @throws TagException
     */
    public function render(array $attributes = []): string
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
     * @param array|object $options
     *
     * @return ElementInterface
     */
    public function setOptions(array | object $options): ElementInterface
    {
        $this->optionsValues = $options;

        return $this;
    }

    /**
     * Returns an array of prepared attributes for Phalcon\Html\TagFactory
     * helpers according to the element parameters
     *
     * @param array $attributes
     *
     * @return array
     */
    protected function prepareAttributes(array $attributes = []): array
    {
        $name          = $this->name;
        $attributes[0] = $name;

        /**
         * Merge passed parameters with default ones
         */
        $merged = array_merge($this->attributes, $attributes);

        /**
         * Get the current element value
         */
        $value = $this->getValue();

        /**
         * If the widget has a value set it as default value
         */
        if (null !== $value) {
            $merged["value"] = $value;
        }

        return $merged;
    }
}
