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

use Phalcon\Contracts\Forms\FormsTypes;
use Phalcon\Contracts\Html\HtmlTypes;
use Phalcon\Tag\Select as SelectTag;

use function is_array;

/**
 * Component SELECT (choice) for forms
 *
 * @phpstan-import-type forms_attributes from FormsTypes
 * @phpstan-import-type forms_select_options from FormsTypes
 * @phpstan-import-type html_attributes from HtmlTypes
 */
class Select extends AbstractElement
{
    /**
     * @var array|object|null
     *
     * @phpstan-var forms_select_options|object|null
     */
    protected mixed $optionsValues = null;

    /**
     * Constructor
     *
     * @phpstan-param forms_select_options|object|null $options
     * @phpstan-param forms_attributes $attributes
     */
    public function __construct(
        string $name,
        mixed $options = null,
        array $attributes = []
    ) {
        $this->optionsValues = $options;

        parent::__construct($name, $attributes);
    }

    /**
     * Adds an option to the current options
     */
    public function addOption(mixed $option): ElementInterface
    {
        if (null === $this->optionsValues) {
            $this->optionsValues = [];
        }

        if (is_array($this->optionsValues)) {
            if (is_array($option)) {
                foreach ($option as $key => $value) {
                    $this->optionsValues[$key] = $value;
                }
            } else {
                $this->optionsValues[] = $option;
            }
        }

        return $this;
    }

    /**
     * Returns the choices' options
     *
     * @phpstan-return forms_select_options|object|null
     */
    public function getOptions(): mixed
    {
        return $this->optionsValues;
    }

    /**
     * Renders the element widget returning HTML
     *
     * @phpstan-param html_attributes $attributes
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
     * @phpstan-param forms_select_options|object $options
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
     * @phpstan-param html_attributes $attributes
     * @phpstan-return array<array-key, mixed>
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
