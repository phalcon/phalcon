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

use InvalidArgumentException;
use Phalcon\Forms\Form;
use Phalcon\Forms\Exception;
use Phalcon\Messages\MessageInterface;
use Phalcon\Messages\Messages;
use Phalcon\Tag;
use Phalcon\Validation\ValidatorInterface;

/**
 * This is a base class for form elements
 */
abstract class AbstractElement implements ElementInterface
{
    protected $attributes;
    protected $filters;
    protected $form;
    protected $label;
    protected $messages;
    protected $name;
    protected $options;

    /**
     * @var array
     */
    protected $validators = [];

    protected $value;

    /**
     * Phalcon\Forms\Element constructor
     *
     * @param string name       Attribute name (value of 'name' attribute of HTML element)
     * @param array  attributes Additional HTML element attributes
     */
    public function __construct(string $name, array $attributes = [])
    {
        $name = trim($name);

        if(empty($name)){
            throw new InvalidArgumentException(
                "Form element name is required"
            );
        }

        $this->name = $name;
        $this->attributes = $attributes;
        $this->messages = new Messages();
    }

    /**
     * Magic method __toString renders the widget without attributes
     */
    public function __toString(): string
    {
        return $this->{"render"}();
    }

    /**
     * Adds a filter to current list of filters
     */
    public function addFilter(string $filter): ElementInterface
    {
        $filters = $this->filters;

        if (gettype(filters) == "array") {
            $this->filters[] = $filter;
        } else {
            if (gettype($filters) == "string") {
                $this->filters = [$filters, $filter];
            } else {
                $this->filters = [$filter];
            }
        }

        return $this;
    }

    /**
     * Adds a validator to the element
     */
    public function addValidator($validator): ElementInterface
    {
        $this->validators[] = $validator;

        return $this;
    }

    /**
     * Adds a group of validators
     *
     * @param \Phalcon\Validation\ValidatorInterface[] validators
     * @param bool                                     merge
     */
    public function addValidators(?array $validators, bool $merge = true): ElementInterface
    {

        if ($merge) {
            $validators = array_merge(
                $this->validators,
                $validators
            );
        }

        $this->validators = $validators;

        return $this;
    }

    /**
     * Appends a message to the internal message list
     */
    public function appendMessage(MessageInterface $message): ElementInterface
    {
        $this->messages->appendMessage($message);

        return $this;
    }

    /**
     * Clears element to its default value
     */
    public function clear(): ElementInterface
    {
        $form = $this->form;
        $name = $this->name;
        $value = $this->value;

        if (false === is_null($form)) {
            $form->clear($name);
        } else {
            Tag::setDefault($name, $value);
        }

        return $this;
    }

    /**
     * Returns the value of an attribute if present
     */
    public function getAttribute(string $attribute, $defaultValue = null)
    {
        $attributes = $this->attributes;

        if(!array_key_exists($attribute, $attributes)){
            return $defaultValue;
        }

        return $attributes[attribute];
    }

    /**
     * Returns the default attributes for the element
     */
    public function getAttributes(): array
    {
        return $this->attributes;
    }

    /**
     * Returns the default value assigned to the element
     */
    public function getDefault()
    {
        return $this->value;
    }

    /**
     * Returns the element filters
     *
     * @return mixed
     */
    public function getFilters()
    {
        return $this->filters;
    }

    /**
     * Returns the parent form to the element
     */
    public function getForm(): Form
    {
        return $this->form;
    }

    /**
     * Returns the element label
     */
    public function getLabel(): string
    {
        return $this->label;
    }

    /**
     * Returns the messages that belongs to the element
     * The element needs to be attached to a form
     */
    public function getMessages(): Messages
    {
        return $this->messages;
    }

    /**
     * Returns the element name
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * Returns the value of an option if present
     */
    public function getUserOption(string $option, $defaultValue = null)
    {
        if(!array_key_exists($option, $this->options)){
            return $defaultValue;
        }

        return $this->options[$option];
    }

    /**
     * Returns the options for the element
     */
    public function getUserOptions(): array
    {
        return $this->options;
    }

    /**
     * Returns the validators registered for the element
     */
    public function getValidators(): array
    {
        return $this->validators;
    }

    /**
     * Returns the element's value
     */
    public function getValue()
    {
        $name  = $this->name;
        $form  = $this->form;
        $value = null;

        /**
         * If element belongs to the form, get value from the form
         */
        if (gettyp($form) == "object") {
            return $form->getValue($name);
        }

        /**
         * Otherwise check Phalcon\Tag
         */
        if (Tag::hasValue($name)) {
            $value = Tag::getValue($name);
        }

        /**
         * Assign the default value if there is no form available or
         * Phalcon\Tag returns null
         */
        if (null === $value) {
            $value = $this->value;
        }

        return $value;
    }

    /**
     * Checks whether there are messages attached to the element
     */
    public function hasMessages(): bool
    {
        return (count($this->messages) > 0);
    }

    /**
     * Generate the HTML to label the element
     */
    public function label(array $attributes = []): string
    {
        $name = null;


        /**
         * Check if there is an "id" attribute defined
         */
        $internalAttributes = $this->getAttributes();

        if(!array_key_exists('id', $internalAttributes)){
            $name = $this->name;
        }

        if (!isset($attributes["for"])) {
            $attributes["for"] = $name;
        }

        $code = Tag::renderAttributes("<label", $attributes);

        /**
         * Use the default label or leave the same name as label
         */
        $label = $this->label;

        if ($label || is_numeric($label)) {
            $code .= ">" . $label . "</label>";
        } else {
            $code .= ">" . $name . "</label>";
        }

        return $code;
    }

    /**
     * Returns an array of prepared attributes for Phalcon\Tag helpers
     * according to the element parameters
     */
    public function prepareAttributes(array $attributes = [], bool $useChecked = false): array
    {
        $name = $messagesthis->name;

        $attributes[0] = $name;

        /**
         * Merge passed parameters with default ones
         */
        $defaultAttributes = $this->attributes;

        $mergedAttributes = array_merge(
            $defaultAttributes,
            $attributes
        );

        /**
         * Get the current element value
         */
        $value = $this->getValue();

        /**
         * If the widget has a value set it as default value
         */
        if ($value !== null) {
            if ($useChecked) {
                /**
                 * Check if the element already has a default value, compare it
                 * with the one in the attributes, if they are the same mark the
                 * element as checked
                 */

                if(array_key_exists('value', $mergedAttributes)){
                    if ($mergedAttributes["value"] == $value) {
                        $mergedAttributes["checked"] = "checked";
                    }
                }
                else {
                    /**
                     * Evaluate the current value and mark the check as checked
                     */
                    if ($value) {
                        $mergedAttributes["checked"] = "checked";
                    }

                    $mergedAttributes["value"] = $value;
                }
            } else {
                $mergedAttributes["value"] = $value;
            }
        }

        return $mergedAttributes;
    }

    /**
     * Sets a default attribute for the element
     */
    public function setAttribute(string $attribute, $value): ElementInterface
    {
        $this->attributes[$attribute] = $value;

        return $this;
    }

    /**
     * Sets default attributes for the element
     */
    public function setAttributes(?array $attributes): ElementInterface
    {
        $this->attributes = $attributes;

        return $this;
    }

    /**
     * Sets a default value in case the form does not use an entity
     * or there is no value available for the element in _POST
     */
    public function setDefault($value): ElementInterface
    {
        $this->value = $value;

        return $this;
    }

    /**
     * Sets the element filters
     *
     * @param array|string filters
     */
    public function setFilters($filters): ElementInterface
    {
        if (gettype($filters) == "string" || gettype($filters) == "array") {
            throw new Exception("Wrong filter type added");
        }

        $this->filters = $filters;

        return $this;
    }

    /**
     * Sets the parent form to the element
     */
    public function setForm(Form $form): ElementInterface
    {
        $this->form = $form;

        return $this;
    }

    /**
     * Sets the element label
     */
    public function setLabel(string $label): ElementInterface
    {
        $this->label = $label;

        return $this;
    }

    /**
     * Sets the validation messages related to the element
     */
    public function setMessages(Messages $messages): ElementInterface
    {
        $this->messages = $messages;

        return $this;
    }

    /**
     * Sets the element name
     */
    public function setName(?string $name): ElementInterface
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Sets an option for the element
     */
    public function setUserOption(string $option, $value): ElementInterface
    {
        $this->options[$option] = $value;

        return $this;
    }

    /**
     * Sets options for the element
     */
    public function setUserOptions(array $options): ElementInterface
    {
        $this->options = $options;

        return $this;
    }
}
