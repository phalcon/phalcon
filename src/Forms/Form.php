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
use Phalcon\Filter\Validation;
use Phalcon\Filter\Validation\Exception as ValidationException;
use Phalcon\Filter\Validation\ValidationInterface;
use Phalcon\Forms\Element\ElementInterface;
use Phalcon\Html\Attributes;
use Phalcon\Html\Attributes\AttributesInterface;
use Phalcon\Html\TagFactory;
use Phalcon\Messages\Messages;
use Phalcon\Traits\Helper\Str\CamelizeTrait;

use function is_string;

/**
 * This component allows to build forms using an object-oriented interface
 */
class Form extends Injectable implements Countable, Iterator, AttributesInterface
{
    use CamelizeTrait;

    /**
     * @var Attributes
     */
    protected Attributes $attributes;

    /**
     * @var array
     */
    protected array $data = [];
    /**
     * @var array
     */
    protected array $elements = [];
    /**
     * @var array
     */
    protected array $elementsIndexed = [];
    /**
     * @var object|null
     */
    protected object | null $entity = null;
    /**
     * @var array
     */
    protected array $filteredData = [];
    /**
     * @var Messages
     */
    protected Messages $messages;
    /**
     * @var array
     */
    protected array $options = [];
    /**
     * @var int
     */
    protected int $position = 0;
    /**
     * @var TagFactory|null
     */
    protected TagFactory | null $tagFactory = null;

    /**
     * @var ValidationInterface|null
     */
    protected ValidationInterface | null $validation = null;

    /**
     * @var array
     */
    protected array $whitelist = [];

    /**
     * Phalcon\Forms\Form constructor
     */
    public function __construct(?object $entity = null, array $userOptions = [])
    {
        $this->entity = $entity;

        /**
         * Update the user options
         */
        $this->options = $userOptions;

        /**
         * Set form attributes/messages
         */
        $this->attributes = new Attributes();
        $this->messages   = new Messages();

        /**
         * Check for an 'initialize' method and call it
         */
        if (true === method_exists($this, "initialize")) {
            $this->initialize($entity, $userOptions);
        }
    }

    /**
     * Adds an element to the form
     */
    public function add(
        ElementInterface $element,
        string | null $position = null,
        bool | null $type = null
    ): Form {
        /**
         * Gets the element's name
         */
        $name = $element->getName();

        /**
         * Link the element to the form
         */
        $element->setForm($this);
        if (
            true === method_exists($element, "setTagFactory") &&
            null !== $this->tagFactory
        ) {
            $element->setTagFactory($this->tagFactory);
        }

        if (null === $position || empty($this->elements)) {
            /**
             * Append the element by its name
             */
            $this->elements[$name] = $element;
        } else {
            $elements = [];

            /**
             * Walk elements and add the element to a particular position
             */
            foreach ($this->elements as $key => $value) {
                $elements = $this->processElementByPosition(
                    $key,
                    $position,
                    $type,
                    $element,
                    $elements,
                    $name,
                    $value
                );
            }

            $this->elements = $elements;
        }

        return $this;
    }

    /**
     * Binds data to the entity
     *
     * @param array       $data
     * @param object|null $entity
     * @param array       $whitelist
     *
     * @return $this
     * @throws Exception
     */
    public function bind(
        array $data,
        ?object $entity = null,
        array $whitelist = []
    ): Form {
        if (empty($this->elements)) {
            throw new Exception("There are no elements in the form");
        }

        if (empty($whitelist)) {
            $whitelist = $this->whitelist;
        }

        $filter       = null;
        $assignData   = [];
        $filteredData = [];
        foreach ($data as $key => $value) {
            /**
             * Get the element
             */
            if (!isset($this->elements[$key])) {
                continue;
            }

            /**
             * Check if the item is in the whitelist
             */
            if (
                !empty($whitelist) &&
                true !== in_array($key, $whitelist)
            ) {
                continue;
            }

            /**
             * Check if the method has filters
             */
            $element       = $this->elements[$key];
            $filters       = $element->getFilters();
            $filteredValue = $value;
            if (!empty($filters)) {
                if (null === $filter) {
                    $container = $this->getDI();
                    $filter    = $container->getShared("filter");
                }

                /**
                 * Sanitize the filters
                 */
                $filteredValue = $filter->sanitize($value, $filters);
            }

            $assignData[$key]   = $value;
            $filteredData[$key] = $filteredValue;

            if (null !== $entity) {
                /**
                 * Use the setter if any available
                 */
                $method = "set" . $this->toCamelize($key);
                if (true === method_exists($entity, $method)) {
                    $entity->{$method}($filteredValue);

                    continue;
                }

                /**
                 * Use the public property if it doesn't have a setter
                 */
                $entity->{$key} = $filteredValue;
            }
        }

        $this->data         = $assignData;
        $this->filteredData = $filteredData;

        return $this;
    }

    /**
     * Clears every element in the form to its default value
     *
     * @param array|string|null $fields
     *
     * @return $this
     */
    public function clear(array | string | null $fields = null): Form
    {
        $data = $this->data;

        /**
         * If fields is string, clear just that field.
         * If it's array, clear only fields in array.
         * If null, clear all
         */
        if (null === $fields) {
            $data = [];

            foreach ($this->elements as $element) {
                $data[$element->getName()] = $element->getDefault();
            }
        } else {
            if (is_string($fields)) {
                $fields = [$fields];
            }

            foreach ($fields as $field) {
                if (isset($data[$field])) {
                    unset($data[$field]);
                }

                if (isset($this->elements[$field])) {
                    $element = $this->elements[$field];

                    $data[$element->getName()] = $element->getDefault();
                }
            }
        }

        $this->data = $data;

        return $this;
    }

    /**
     * Returns the number of elements in the form
     *
     * @return int
     */
    public function count(): int
    {
        return count($this->elements);
    }

    /**
     * Returns the current element in the iterator
     *
     * @return mixed
     */
    public function current(): mixed
    {
        if (!isset($this->elementsIndexed[$this->position])) {
            return false;
        }

        return $this->elementsIndexed[$this->position];
    }

    /**
     * Returns an element added to the form by its name
     *
     * @param string $name
     *
     * @return ElementInterface
     * @throws Exception
     */
    public function get(string $name): ElementInterface
    {
        if (!isset($this->elements[$name])) {
            throw new Exception(
                "Element with ID=" . $name . " is not part of the form"
            );
        }

        return $this->elements[$name];
    }

    /**
     * Returns the form's action
     *
     * @return string
     */
    public function getAction(): string
    {
        return (string)$this->getAttributes()->get("action");
    }

    /**
     * Get Form attributes collection
     *
     * @return Attributes
     */
    public function getAttributes(): Attributes
    {
        return $this->attributes;
    }

    /**
     * Returns the form elements added to the form
     *
     * @return ElementInterface[]
     */
    public function getElements(): array
    {
        return $this->elements;
    }

    /**
     * Returns the entity related to the model
     *
     * @return object|null
     */
    public function getEntity(): object | null
    {
        return $this->entity;
    }

    /**
     * Gets a value from the internal filtered data or calls getValue(name)
     *
     * @param string $name
     *
     * @return mixed
     */
    public function getFilteredValue(string $name): mixed
    {
        return $this->filteredData[$name] ?? $this->getValue($name);
    }

    /**
     * Returns a label for an element
     *
     * @param string $name
     *
     * @return string
     * @throws Exception
     */
    public function getLabel(string $name): string
    {
        $element = $this->get($name);
        $label   = $element->getLabel();

        /**
         * Use the element's name as label if the label is not available
         */
        return !$label ? $name : $label;
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
     *
     * @return Messages
     */
    public function getMessages(): Messages
    {
        return $this->messages;
    }

    /**
     * Returns the messages generated for a specific element
     *
     * @param string $name
     *
     * @return Messages
     * @throws Exception
     */
    public function getMessagesFor(string $name): Messages
    {
        if (true !== $this->has($name)) {
            return new Messages();
        }

        return $this->get($name)->getMessages();
    }

    /**
     * Returns the tagFactory object
     *
     * @return TagFactory|null
     */
    public function getTagFactory(): TagFactory | null
    {
        return $this->tagFactory;
    }

    /**
     * Returns the value of an option if present
     *
     * @param string     $option
     * @param mixed|null $defaultValue
     *
     * @return mixed
     */
    public function getUserOption(
        string $option,
        mixed $defaultValue = null
    ): mixed {
        return $this->options[$option] ?? $defaultValue;
    }

    /**
     * Returns the options for the element
     *
     * @return array
     */
    public function getUserOptions(): array
    {
        return $this->options;
    }

    /**
     * @return ValidationInterface|null
     */
    public function getValidation(): ValidationInterface | null
    {
        return $this->validation;
    }

    /**
     * Gets a value from the internal related entity or from the default value
     *
     * @param string $name
     *
     * @return mixed
     */
    public function getValue(string $name): mixed
    {
        $method = "get" . $this->toCamelize($name);

        /**
         * Check if form has a getter
         */
        if (true === method_exists($this, "getCustomValue")) {
            return $this->getCustomValue($name, $this->entity, $this->data);
        }

        if (null !== $this->entity) {
            /**
             * Check if the entity has a getter
             */
            if (true === method_exists($this->entity, $method)) {
                return $this->entity->{$method}();
            }

            /**
             * Check if the entity has a public property
             */
            if (isset($this->entity->{$name})) {
                return $this->entity->{$name};
            }
        }

        /**
         * Check if the data is in the data array
         */
        if (isset($this->data[$name])) {
            return $this->data[$name];
        }

        $forbidden = [
            "action"        => true,
            "attributes"    => true,
            "di"            => true,
            "elements"      => true,
            "entity"        => true,
            "eventsmanager" => true,
            "label"         => true,
            "messages"      => true,
            "messagesfor"   => true,
            "tagFactory"    => true,
            "useroption"    => true,
            "useroptions"   => true,
            "validation"    => true,
            "value"         => true,
        ];

        /**
         * Check if the method is internal
         */
        if (isset($forbidden[strtolower($name)])) {
            return null;
        }

        /**
         * Check if form has a getter
         */
        if (true === method_exists($this, $method)) {
            return $this->{$method}();
        }

        /**
         * Check if element has default value
         */
        if (isset($this->elements[$name])) {
            $element = $this->elements[$name];

            return $element->getDefault();
        }

        return null;
    }

    /**
     * @return array
     */
    public function getWhitelist(): array
    {
        return $this->whitelist;
    }

    /**
     * Check if the form contains an element
     *
     * @param string $name
     *
     * @return bool
     */
    public function has(string $name): bool
    {
        return isset($this->elements[$name]);
    }

    /**
     * Check if messages were generated for a specific element
     *
     * @param string $name
     *
     * @return bool
     * @throws Exception
     */
    public function hasMessagesFor(string $name): bool
    {
        return $this->getMessagesFor($name)->count() > 0;
    }

    /**
     * Validates the form
     *
     * @param array       $data
     * @param object|null $entity
     * @param array       $whitelist
     *
     * @return bool
     * @throws Exception
     * @throws ValidationException
     */
    public function isValid(
        array $data = [],
        ?object $entity = null,
        array $whitelist = []
    ): bool {
        if (empty($this->elements)) {
            return true;
        }
        $whitelist = empty($whitelist) ? $this->whitelist : $whitelist;
        $data      = empty($data) ? $this->data : $data;
        $entity    = null === $entity ? $this->entity : $entity;

        $this->bind($data, $entity, $whitelist);

        /**
         * Check if there is a method 'beforeValidation'
         */
        if (
            true === method_exists($this, "beforeValidation") &&
            false === $this->beforeValidation($data, $entity)
        ) {
            return false;
        }

        $validationStatus = true;
        $validation       = $this->getValidation();

        if (null === $validation) {
            // Create an implicit validation
            $validation = new Validation();
        }

        /** @var ElementInterface $element */
        foreach ($this->elements as $element) {
            $validators = $element->getValidators();

            if (0 === count($validators)) {
                continue;
            }

            /**
             * Element's name
             */
            $name = $element->getName();

            /**
             * Append (not overriding) element validators to validation class
             */
            foreach ($validators as $validator) {
                $validation->add($name, $validator);
            }

            /**
             * Get filters in the element
             */
            $filters = $element->getFilters();

            /**
             * Assign the filters to the validation
             */
            if (!empty($filters)) {
                $validation->setFilters($name, $filters);
            }
        }

        /**
         * Perform the validation
         */
        $validation->validate($data, $entity);
        $messages = $validation->getMessages();
        if ($messages->count() > 0) {
            // Add validation messages to relevant elements
            foreach ($messages as $elementMessage) {
                $this->get($elementMessage->getField())
                     ->appendMessage($elementMessage)
                ;
            }

            $messages->rewind();

            $validationStatus = false;
        }

        /**
         * If the validation fails update the messages
         */
        if (true !== $validationStatus) {
            $this->messages = $messages;
        }

        /**
         * Check if there is a method 'afterValidation'
         */
        if (true === method_exists($this, "afterValidation")) {
            $this->afterValidation($messages);
        }

        /**
         * Return the validation status
         */
        return $validationStatus;
    }

    /**
     * Returns the current position/key in the iterator
     *
     * @return int
     */
    public function key(): int
    {
        return $this->position;
    }

    /**
     * Generate the label of an element added to the form including HTML
     *
     * @param string $name
     * @param array  $attributes
     *
     * @return string
     * @throws Exception
     */
    public function label(string $name, array $attributes = []): string
    {
        $element = $this->get($name);

        return $element->label($attributes);
    }

    /**
     * Moves the internal iteration pointer to the next position
     *
     * @return void
     */
    public function next(): void
    {
        $this->position++;
    }

    /**
     * Removes an element from the form
     *
     * @param string $name
     *
     * @return bool
     */
    public function remove(string $name): bool
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
        $this->elementsIndexed = [];

        return false;
    }

    /**
     * Renders a specific item in the form
     *
     * @param string $name
     * @param array  $attributes
     *
     * @return string
     * @throws Exception
     */
    public function render(string $name, array $attributes = []): string
    {
        $element = $this->get($name);

        return $element->render($attributes);
    }

    /**
     * Rewinds the internal iterator
     *
     * @return void
     */
    public function rewind(): void
    {
        $this->position        = 0;
        $this->elementsIndexed = array_values($this->elements);
    }

    /**
     * Sets the form's action
     *
     * @param string $action
     *
     * @return $this
     */
    public function setAction(string $action): Form
    {
        $this->getAttributes()->set("action", $action);

        return $this;
    }

    /**
     * Set form attributes collection
     *
     * @param Attributes $attributes
     *
     * @return $this
     */
    public function setAttributes(Attributes $attributes): Form
    {
        $this->attributes = $attributes;

        return $this;
    }

    /**
     * Sets the entity related to the model
     *
     * @param object $entity
     *
     * @return $this
     */
    public function setEntity(object $entity): Form
    {
        $this->entity = $entity;

        return $this;
    }

    /**
     * Sets the tagFactory for the form
     *
     * @param TagFactory $tagFactory
     *
     * @return $this
     */
    public function setTagFactory(TagFactory $tagFactory): Form
    {
        $this->tagFactory = $tagFactory;

        return $this;
    }

    /**
     * Sets an option for the form
     *
     * @param string $option
     * @param mixed  $value
     *
     * @return $this
     */
    public function setUserOption(string $option, mixed $value): Form
    {
        $this->options[$option] = $value;

        return $this;
    }

    /**
     * Sets options for the element
     *
     * @param array $options
     *
     * @return $this
     */
    public function setUserOptions(array $options): Form
    {
        $this->options = $options;

        return $this;
    }

    /**
     * Sets the default validation
     *
     * @param ValidationInterface $validation
     *
     * @return $this
     */
    public function setValidation(ValidationInterface $validation): Form
    {
        $this->validation = $validation;

        return $this;
    }

    /**
     * Sets the default whitelist
     *
     * @param array $whitelist
     *
     * @return $this
     */
    public function setWhitelist(array $whitelist): Form
    {
        $this->whitelist = $whitelist;

        return $this;
    }

    /**
     * Check if the current element in the iterator is valid
     *
     * @return bool
     */
    public function valid(): bool
    {
        return isset($this->elementsIndexed[$this->position]);
    }

    /**
     * @param int|string       $key
     * @param string           $position
     * @param bool|null        $type
     * @param ElementInterface $element
     * @param mixed            $elements
     * @param string           $name
     * @param mixed            $value
     *
     * @return array|mixed
     */
    private function processElementByPosition(
        int | string $key,
        string $position,
        ?bool $type,
        ElementInterface $element,
        mixed $elements,
        string $name,
        mixed $value
    ): mixed {
        if ($key === $position) {
            $elements = $this->processElements(
                $type,
                $element,
                $elements,
                $name,
                $value,
                $key
            );
        } else {
            /**
             * Copy the element to new array
             */
            $elements[$key] = $value;
        }
        return $elements;
    }

    /**
     * @param bool|null        $type
     * @param ElementInterface $element
     * @param array            $elements
     * @param string           $name
     * @param mixed            $value
     * @param string           $key
     *
     * @return array
     */
    private function processElements(
        ?bool $type,
        ElementInterface $element,
        array $elements,
        string $name,
        mixed $value,
        string $key
    ): array {
        if (true === $type) {
            /**
             * Add the element before position
             */
            $elements[$name] = $element;
            $elements[$key]  = $value;
        } else {
            /**
             * Add the element after position
             */
            $elements[$key]  = $value;
            $elements[$name] = $element;
        }

        return $elements;
    }
}
