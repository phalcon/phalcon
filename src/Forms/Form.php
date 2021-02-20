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

namespace Phalcon\Forms;

use Countable;
use Iterator;
use Phalcon\Di\Injectable;
use Phalcon\Di\DiInterface;
use Phalcon\Filter\FilterInterface;
use Phalcon\Forms\Element\ElementInterface;
use Phalcon\Html\Attributes;
use Phalcon\Html\Attributes\AttributesInterface;
use Phalcon\Messages\Messages;
use Phalcon\Tag;
use Phalcon\Validation;
use Phalcon\Validation\ValidationInterface;

use Phalcon\Exception;

/**
 * This component allows to build forms using an object-oriented interface
 */
class Form extends Injectable implements Countable, Iterator, AttributesInterface
{
    /**
     * @var Attributes | null
     */
    protected $attributes = null;

    protected $data;

    protected $elements = [];

    protected $elementsIndexed;

    protected $entity;

    protected $messages;

    protected $position;

    protected $options;

    protected $validation;

    public function getValidation(){
        return $this->validation;
    }

    public function setValisation($validation){
        $this->validation = $validation;
    }

    /**
     * Phalcon\Forms\Form constructor
     */
    public function __construct($entity = null, $userOptions = [])
    {

        if ($entity === null && gettype($entity) == "object") {
            throw new Exception("The base entity is not valid");
        }

        $this->entity = $entity;

        /**
         * Update the user options
         */
        $this->options = $userOptions;

        /**
        * Set form attributes
        */
        $this->attributes = new Attributes();

        /**
         * Check for an 'initialize' method and call it
         */
        if (method_exists($this, "initialize")) {
            $this->{"initialize"}($entity, $userOptions);
        }
    }

    /**
     * Adds an element to the form
     */
    public function add(ElementInterface $element, string $position = null, bool $type = null): Form
    {
        /**
         * Gets the element's name
         */
        $name = $element->getName();

        /**
         * Link the element to the form
         */
        $element->setForm($this);

        if ($position == null || empty($this->elements)) {
            /**
             * Append the element by its name
             */
            $this->elements[$name] = $element;
        } else {
            $elements = [];

            /**
             * Walk elements and add the element to a particular position
             */
            foreach($this->elements as $key => $value) {
                if ($key == $position) {
                    if ($type) {
                        /**
                         * Add the element before position
                         */
                        $elements[$name] = $element;
                        $elements[$key] = $value;
                    } else {
                        /**
                         * Add the element after position
                         */
                        $elements[$key] = $value;
                        $elements[$name] = $element;
                    }
                } else {
                    /**
                     * Copy the element to new array
                     */
                    $elements[$key] = $value;
                }
            }

            $this->elements = $elements;
        }

        return $this;
    }

    /**
     * Binds data to the entity
     *
     * @param object entity
     * @param array whitelist
     */
    public function bind(?array $data, $entity, $whitelist = null): Form
    {
        if (empty($this->elements)) {
            throw new Exception("There are no elements in the form");
        }

        $filter = null;

        foreach ($data as $key => $value) {
            /**
             * Get the element
             */
            if(false === array_key_exists($key, $this->elements)){
                continue;
            }

            /**
             * Check if the item is in the whitelist
             */
            if('array' === gettype($whitelist) && !in_array($key, $whitelist)){
                continue;
            }

            /**
             * Check if the method has filters
             */
            $filters = $element->getFilters();

            if ($filters) {
                if (gettype($filter) != "object") {
                    $container = $this->getDI();
                    $filter = $container->getShared("filter");
                }

                /**
                 * Sanitize the filters
                 */
                $filteredValue = $filter->sanitize($value, $filters);
            } else {
                $filteredValue = $value;
            }

            /**
             * Use the setter if any available
             */
            $method = "set" . camelize($key);
            if (method_exists($entity, $method)) {
                $entity->{$method}($filteredValue);

                continue;
            }

            /**
             * Use the public property if it doesn't have a setter
             */
            $entity->{$key} = $filteredValue;
        }

        $this->data = $data;

        return $this;
    }

    /**
     * Clears every element in the form to its default value
     *
     * @param array|string|null fields
     */
    public function clear($fields = null): Form
    {
        $data = $this->data;
        $elements = $this->elements;

        /**
         * If fields is string, clear just that field.
         * If it's array, clear only fields in array.
         * If null, clear all
         */
        if ($fields === null) {
            $data = [];

            foreach($element as $elements) {
                Tag::setDefault(
                    $element->getName(),
                    $element->getDefault()
                );
            }
        } else {
            if (gettype($fields) != "array") {
                $fields = [$fields];
            }

            foreach($field as $fields) {
                if (isset($data[$field])) {
                    unset($data[$field]);
                }

                if(array_key_exists($field, $elements)){
                    $element = $elements[$field];

                    Tag::setDefault(
                        $element->getName(),
                        $element->getDefault()
                    );
                }
            }
        }

        $this->data = $data;

        return $this;
    }

    /**
     * Returns the number of elements in the form
     */
    public function count(): int
    {
        return count($this->elements);
    }

    /**
     * Returns the current element in the iterator
     */
    public function current()
    {
        $element = null;

        if(array_key_exists($this->position, $this->elementsIndexed)){
            return $this->elementsIndexed[$this->position];
        }
        else{
            return false;
        }
    }

    /**
     * Returns an element added to the form by its name
     */
    public function get(?string $name): ElementInterface
    {
        if(array_key_exists($name, $this->elements)){
            return $this->elements[$name];
        }
        else{
            throw new Exception(
                "Element with ID=" . $name . " is not part of the form"
            );
        }
    }

    /**
     * Returns the form's action
     */
    public function getAction(): string
    {
        return (string) $this->getAttributes()->get("action");
    }

    /**
    * Get Form attributes collection
    */
    public function getAttributes(): Attributes
    {
        if(null === $this->attributes){
            $this->attributes = new Attributes();
        }

        return $this->attributes;
    }

    /**
     * Returns the form elements added to the form
     */
    public function getElements()
    {
        return $this->elements;
    }

    /**
     * Returns the entity related to the model
     *
     * @return object
     */
    public function getEntity()
    {
        return $this->entity;
    }

    /**
     * Returns a label for an element
     */
    public function getLabel(?string $name): string
    {
        $element = null;
        if(array_key_exists($name, $this->elements)){
            $element = $this->elements[$name];
        }
        else{
            throw new Exception(
                "Element with ID=" . $name . " is not part of the form"
            );
        }

        $label = $element->getLabel();

        /**
         * Use the element's name as label if the label is not available
         */
        if (!$label) {
            return $name;
        }

        return $label;
    }

    /**
     * Returns the messages generated in the validation.
     *
     * ```php
     * if ($form->isValid($_POST) == false) {
     *     $messages = $form->getMessages();
     *
     *     foreach ($messages as $message) {
     *         echo $message, "<br>";
     *     }
     * }
     * ```
     */
    public function getMessages()
    {
        $messages = $this->messages;

        if (!(gettype($messages) == "object" && $messages instanceof Messages)) {
            return new Messages();
        }

        return $messages;
    }

    /**
     * Returns the messages generated for a specific element
     */
    public function getMessagesFor(?string $name): Messages
    {
        if (!$this->has($name)) {
            return new Messages();
        }

        return $this->get($name)->getMessages();
    }

    /**
     * Returns the value of an option if present
     */
    public function getUserOption(string $option, $defaultValue = null)
    {
        $value = null;

        if(!array_key_exists($option, $this->options)){
            return $defaultValue;
        }
        else{
            return $this->options[$option];
        }
    }

    /**
     * Returns the options for the element
     */
    public function getUserOptions(): array
    {
        return $this->options;
    }

    /**
     * Gets a value from the internal related entity or from the default value
     */
    public function getValue(?string $name)
    {
        $entity = $this->entity;
        $data   = $this->data;
        $method = null;

        /**
         * Check if form has a getter
         */
        if (method_exists($this, "getCustomValue")) {
            return $this->{"getCustomValue"}($name, $entity, $data);
        }

        if (gettype(entity) == "object") {
            /**
             * Check if the entity has a getter
             */
            $method = "get" . camelize($name);
            if (method_exists($entity, $method)) {
                return $entity->{$method}();
            }

            /**
             * Check if the entity has a public property
             */
            if(property_exists($name, $entity)){
                return $entity->{$name};
            }
        }

        if(gettype($data) == "array") {
            /**
             * Check if the data is in the data array
             */
            if(array_key_exists($name, $data)){
                return $data[$name];
            }
        }

        $forbidden = [
            "attributes"    => true,
            "validation"    => true,
            "action"        => true,
            "useroption"    => true,
            "useroptions"   => true,
            "entity"        => true,
            "elements"      => true,
            "messages"      => true,
            "messagesfor"   => true,
            "label"         => true,
            "value"         => true,
            "di"            => true,
            "eventsmanager" => true
        ];

        /**
         * Check if the method is internal
         */
        $internalEntity = strtolower($name);
        if (isset($forbidden[$internalEntity])) {
            return null;
        }

        /**
         * Check if form has a getter
         */
        $method = "get" . camelize($name);
        if(method_exists($this, $method)) {
            return $this->{$method}();
        }

        /**
         * Check if the tag has a default value
         */
        if (Tag::hasValue($name)) {
            return Tag::getValue($name);
        }

        /**
         * Check if element has default value
         */
        if(array_key_exists($name, $this->elements)){
            return $this->elements[$name]->getDefault();
        }

        return null;
    }

    /**
     * Check if the form contains an element
     */
    public function has(?string $name): bool
    {
        /**
         * Checks if the element is in the form
         */
        return (isset($this->elements[$name]));
    }

    /**
     * Check if messages were generated for a specific element
     */
    public function hasMessagesFor(?string $name): bool
    {
        return ($this->getMessagesFor($name)->count() > 0);
    }

    /**
     * Validates the form
     *
     * @param array data
     * @param object entity
     */
    public function isValid($data = null, $entity = null): bool
    {
        if (empty($this->elements)) {
            return true;
        }

        /**
         * If the data is not an array use the one passed previously
         */
        $data = null;
        if (gettype(data) != "array") {
            $data = $this->data;
        }

        /**
         * If the user doesn't pass an entity we use the one in this_ptr->entity
         */
        if (gettype($entity) == "object") {
            $this->bind($data, $entity);
        } else {
            if (gettype($this->entity) == "object") {
                $this->bind($data, $this->entity);
            }
        }

        /**
         * Check if there is a method 'beforeValidation'
         */
        if (method_exists($this, "beforeValidation")) {
            if($this->{"beforeValidation"}($data, $entity) === false) {
                return false;
            }
        }

        $validationStatus = true;

        $validation = $this->getValidation();

        if ((gettype($validation) != "object") || !($validation instanceof ValidationInterface)) {
            // Create an implicit validation
            $validation = new Validation();
        }

        foreach($element as $this->elements) {
            $validators = $element->getValidators();

            if (count($validators) == 0) {
                continue;
            }

            /**
             * Element's name
             */
            $name = $element->getName();

            /**
            * Append (not overriding) element validators to validation class
            */
            foreach($validators as $validator) {
                $validation->add($name, $validator);
            }

            /**
             * Get filters in the element
             */
            $filters = $element->getFilters();

            /**
             * Assign the filters to the validation
             */
            if(gettype(filters) == "array") {
                $validation->setFilters($name, $filters);
            }
        }

        /**
        * Perform the validation
        */
        $messages = $validation->validate($data, $entity);
        if($messages->count()) {
            // Add validation messages to relevant elements
            foreach($message as $elementMessage) {
                $this->get($elementMessage->getField())->appendMessage($elementMessage);
            }

            $messages->rewind();

            $validationStatus = false;
        }

        /**
         * If the validation fails update the messages
         */
        if (!$validationStatus) {
            $this->messages = $messages;
        }

        /**
         * Check if there is a method 'afterValidation'
         */
        if (method_exists($this, "afterValidation")) {
            $this->{"afterValidation"}($messages);
        }

        /**
         * Return the validation status
         */
        return $validationStatus;
    }

    /**
     * Returns the current position/key in the iterator
     */
    public function key(): int
    {
        return $this->position;
    }

    /**
     * Generate the label of an element added to the form including HTML
     */
    public function label(?string $name, array $attributes = null): string
    {
        if(array_key_exists($name, $this->elements)){
            return $this->elements[$name]->label($attributes);
        }
        else{
            throw new Exception(
                "Element with ID=" . $name . " is not part of the form"
            );
        }
    }

    /**
     * Moves the internal iteration pointer to the next position
     */
    public function next(): void
    {
        $this->position++;
    }

    /**
     * Renders a specific item in the form
     */
    public function render(?string $name, array $attributes = []): string
    {
        if(array_key_exists($name, $this->elements)){
            return $this->elements[$name]->render($attributes);
        }
        else{
            throw new Exception(
                "Element with ID=" . $name . " is not part of the form"
            );
        }
    }

    /**
     * Removes an element from the form
     */
    public function remove(?string $name): bool
    {
        /**
         * Checks if the element is in the form
         */
        if (isset($this->elements[$name])) {
            unset($this->elements[$name]);

            return true;
        }

        /**
         * Clean the iterator index
         */
        $this->elementsIndexed = null;

        return false;
    }

    /**
     * Rewinds the internal iterator
     */
    public function rewind(): void
    {
        $this->position = 0;

        $this->elementsIndexed = array_values($this->elements);
    }

    /**
     * Sets the form's action
     *
     * @return Form
     */
    public function setAction(?string $action): Form
    {
        $this->getAttributes()->set("action", $action);

        return $this;
    }

    /**
     * Sets the entity related to the model
     *
     * @param object entity
     */
    public function setEntity($entity): Form
    {
        $this->entity = $entity;

        return $this;
    }

    /**
    * Set form attributes collection
    */
    public function setAttributes(Attributes $attributes): AttributesInterface
    {
        $this->attributes = $attributes;

        return $this;
    }

    /**
     * Sets an option for the form
     */
    public function setUserOption(string $option, $value): Form
    {
        $this->options[$option] = $value;

        return $this;
    }

    /**
     * Sets options for the element
     */
    public function setUserOptions(?array $options): Form
    {
        $this->options = $options;

        return $this;
    }

    /**
     * Check if the current element in the iterator is valid
     */
    public function valid(): bool
    {
        return (isset($this->elementsIndexed[$this->position]));
    }
}
