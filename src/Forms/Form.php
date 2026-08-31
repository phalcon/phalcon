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
use Phalcon\Contracts\Forms\FormsTypes;
use Phalcon\Contracts\Forms\Schema;
use Phalcon\Contracts\Html\HtmlTypes;
use Phalcon\Di\DiInterface;
use Phalcon\Di\Injectable;
use Phalcon\Filter\FilterInterface;
use Phalcon\Filter\Validation;
use Phalcon\Filter\Validation\ValidationInterface;
use Phalcon\Forms\Element\Check;
use Phalcon\Forms\Element\ElementInterface;
use Phalcon\Forms\Exceptions\ElementNotInForm;
use Phalcon\Forms\Exceptions\InvalidEntity;
use Phalcon\Forms\Exceptions\NoFormElements;
use Phalcon\Html\Attributes;
use Phalcon\Html\Attributes\AttributesInterface;
use Phalcon\Html\TagFactory;
use Phalcon\Messages\Messages;
use Phalcon\Support\Settings;
use Phalcon\Traits\Support\Helper\Str\CamelizeTrait;

use function array_key_exists;
use function is_string;
use function strtolower;

/**
 * This component allows to build forms using an object-oriented interface
 *
 * @phpstan-import-type forms_data from FormsTypes
 * @phpstan-import-type forms_elements from FormsTypes
 * @phpstan-import-type forms_elements_indexed from FormsTypes
 * @phpstan-import-type forms_options from FormsTypes
 * @phpstan-import-type forms_schema_definition from FormsTypes
 * @phpstan-import-type forms_whitelist from FormsTypes
 * @phpstan-import-type html_attributes from HtmlTypes
 *
 * @implements Iterator<int, ElementInterface>
 */
class Form extends Injectable implements Countable, Iterator, AttributesInterface
{
    use CamelizeTrait;

    /**
     * @var Attributes
     */
    protected Attributes $attributes;

    /**
     * @phpstan-var forms_data
     */
    protected array $data = [];
    /**
     * @phpstan-var forms_elements
     */
    protected array $elements = [];
    /**
     * @phpstan-var forms_elements_indexed
     */
    protected array $elementsIndexed = [];
    /**
     * @var object|null
     */
    protected object | null $entity = null;
    /**
     * @phpstan-var forms_data
     */
    protected array $filteredData = [];
    protected Messages $messages;
    /**
     * @phpstan-var forms_options
     */
    protected array $options = [];
    protected int $position = 0;
    protected ?TagFactory $tagFactory = null;
    protected ?ValidationInterface $validation = null;
    /**
     * @phpstan-var forms_whitelist
     */
    protected array $whitelist = [];

    /**
     * Phalcon\Forms\Form constructor
     *
     * @phpstan-param forms_options $userOptions
     */
    public function __construct(mixed $entity = null, array $userOptions = [])
    {
        if ($entity !== null && !is_object($entity)) {
            throw new InvalidEntity();
        }

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
    ): static {
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
                if ($key == $position) {
                    if ($type) {
                        $elements[$name] = $element;
                        $elements[$key]  = $value;
                    } else {
                        $elements[$key]  = $value;
                        $elements[$name] = $element;
                    }
                } else {
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
     * @phpstan-param forms_data      $data
     * @phpstan-param object|null     $entity
     * @phpstan-param forms_whitelist $whitelist
     */
    public function bind(
        array $data,
        ?object $entity = null,
        array $whitelist = []
    ): static {
        if (empty($this->elements)) {
            throw new NoFormElements();
        }

        /**
         * Check if there is a method 'beforeBind'
         */
        if (true === method_exists($this, "beforeBind")) {
            if (false === $this->{"beforeBind"}($data, $entity)) {
                return $this;
            }
        }

        if (empty($whitelist)) {
            $whitelist = $this->whitelist;
        }

        /**
         * Unchecked checkboxes are absent from POST data. For any Check
         * element that opted in via setUncheckedValue(), inject the
         * registered value so the existing bind loop applies it to the
         * entity. See cphalcon issue #16982.
         */
        foreach ($this->elements as $elementName => $element) {
            if (
                $element instanceof Check &&
                $element->hasUncheckedValue()
            ) {
                /** @var string $dataKey */
                $dataKey = $element->getAttribute('name') ?? $elementName;
                if (!array_key_exists($dataKey, $data)) {
                    $data[$dataKey] = $element->getUncheckedValue();
                }
            }
        }

        $filter       = null;
        $assignData   = [];
        $filteredData = [];
        foreach ($data as $key => $value) {
            /**
             * Get the element
             */
            $element = $this->elements[$key] ?? null;
            if (null === $element) {
                foreach ($this->elements as $candidate) {
                    if ($candidate->getAttribute('name') === $key) {
                        $element = $candidate;
                        break;
                    }
                }

                if (null === $element) {
                    continue;
                }
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
            $filters       = $element->getFilters();
            $filteredValue = $value;
            if ($filters) {
                if (!is_object($filter)) {
                    /** @var DiInterface $container */
                    $container = $this->getDI();
                    /** @var FilterInterface $filter */
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
                 * Use the public property if it does not have a setter
                 */
                if (!Settings::get("form.strict_entity_property_check")) {
                    $entity->{$key} = $filteredValue;

                    continue;
                }

                if (property_exists($entity, $key)) {
                    $entity->{$key} = $filteredValue;
                }
            }
        }

        $this->data         = $assignData;
        $this->filteredData = $filteredData;

        /**
         * Check if there is a method 'afterBind'
         */
        if (true === method_exists($this, "afterBind")) {
            $this->{"afterBind"}($entity);
        }

        return $this;
    }

    /**
     * Clears every element in the form to its default value
     *
     * @phpstan-param array<array-key, string>|string|null $fields
     */
    public function clear(array | string | null $fields = null): static
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
                /**
                 * array_key_exists() is used so a stored `null` is still
                 * recognized as present and unset before the default is
                 * assigned. [#CP-17042]
                 */
                if (array_key_exists($field, $data)) {
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
     */
    public function get(string $name): ElementInterface
    {
        if (!isset($this->elements[$name])) {
            throw new ElementNotInForm($name);
        }

        return $this->elements[$name];
    }

    /**
     * Returns the form's action
     */
    public function getAction(): string
    {
        /** @var string|null $action */
        $action = $this->getAttributes()->get("action");

        return (string)$action;
    }

    /**
    * Get Form attributes collection
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
     */
    public function getFilteredValue(string $name): mixed
    {
        return $this->filteredData[$name] ?? $this->getValue($name);
    }

    /**
     * Returns a label for an element
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
     */
    public function getMessages(): Messages
    {
        return $this->messages;
    }

    /**
     * Returns the messages generated for a specific element
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
     */
    public function getTagFactory(): TagFactory | null
    {
        return $this->tagFactory;
    }

    /**
     * Returns the value of an option if present
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
     * @phpstan-return forms_options
     */
    public function getUserOptions(): array
    {
        return $this->options;
    }

    /**
     * return ValidationInterface|null
     */
    public function getValidation(): ValidationInterface | null
    {
        return $this->validation;
    }

    /**
     * Gets a value from the internal related entity or from the default value
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
        if (array_key_exists($name, $this->data)) {
            return $this->data[$name];
        }

        /**
         * Check if the name is in the $_POST superglobal
         */
        if (isset($_POST[$name])) {
            return $_POST[$name];
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
     * @phpstan-return forms_whitelist
     */
    public function getWhitelist(): array
    {
        return $this->whitelist;
    }

    /**
     * Check if the form contains an element
     */
    public function has(string $name): bool
    {
        return isset($this->elements[$name]);
    }

    /**
     * Check if messages were generated for a specific element
     */
    public function hasMessagesFor(string $name): bool
    {
        return $this->getMessagesFor($name)->count() > 0;
    }

    /**
     * Validates the form
     *
     * @phpstan-param forms_data      $data
     * @phpstan-param object|null     $entity
     * @phpstan-param forms_whitelist $whitelist
     */
    public function isValid(
        mixed $data = null,
        mixed $entity = null,
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

            /**
             * Get filters in the element
             */
            $filters = $element->getFilters();

            if (empty($validators) && empty($filters)) {
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
     */
    public function key(): int
    {
        return $this->position;
    }

    /**
     * Generate the label of an element added to the form including HTML
     *
     * @phpstan-param html_attributes $attributes
     */
    public function label(string $name, array $attributes = []): string
    {
        $element = $this->get($name);

        return $element->label($attributes);
    }

    /**
     * Loads elements into the form from a Schema source.
     *
     * Each definition in the schema must have at least 'type' and 'name'.
     * The locator resolves the type string to an element factory; custom
     * types can be registered on the locator with setElement().
     *
     * @throws Exception
     */
    public function load(Schema $schema, FormsLocator $locator): static
    {
        /** @var forms_schema_definition $definition */
        foreach ($schema->load() as $definition) {
            $type = strtolower((string) $definition['type']);

            $name       = (string) $definition['name'];
            $attributes = (array) ($definition['attributes'] ?? []);
            $options    = (array) ($definition['options'] ?? []);

            /** @var ElementInterface $element */
            $element = ($locator->getElement($type))($name, $options, $attributes);

            if (!empty($definition['label'])) {
                $element->setLabel((string) $definition['label']);
            }

            if (array_key_exists('default', $definition)) {
                $element->setDefault($definition['default']);
            }

            if (!empty($definition['filters'])) {
                $element->setFilters($definition['filters']);
            }

            if (!empty($definition['validators'])) {
                $element->addValidators($definition['validators']);
            }

            $this->add($element);
        }

        return $this;
    }

    /**
     * Moves the internal iteration pointer to the next position
     */
    public function next(): void
    {
        $this->position++;
    }

    /**
     * Removes an element from the form
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
     * @phpstan-param html_attributes $attributes
     */
    public function render(string $name, array $attributes = []): string
    {
        $element = $this->get($name);

        return $element->render($attributes);
    }

    /**
     * Rewinds the internal iterator
     */
    public function rewind(): void
    {
        $this->position        = 0;
        $this->elementsIndexed = array_values($this->elements);
    }

    /**
     * Sets the form's action
     *
     */
    public function setAction(string $action): static
    {
        $this->getAttributes()->set("action", $action);

        return $this;
    }

    /**
     * Set form attributes collection
     */
    public function setAttributes(Attributes $attributes): static
    {
        $this->attributes = $attributes;

        return $this;
    }

    /**
     * Sets the entity related to the model
     *
     * @param object $entity
     */
    public function setEntity(mixed $entity): static
    {
        $this->entity = $entity;

        return $this;
    }

    /**
     * Sets the tagFactory for the form
     */
    public function setTagFactory(TagFactory $tagFactory): static
    {
        $this->tagFactory = $tagFactory;

        return $this;
    }

    /**
     * Sets an option for the form
     */
    public function setUserOption(string $option, mixed $value): static
    {
        $this->options[$option] = $value;

        return $this;
    }

    /**
     * Sets options for the element
     *
     * @phpstan-param forms_options $options
     */
    public function setUserOptions(array $options): static
    {
        $this->options = $options;

        return $this;
    }

    /**
     * Sets the default validation
     */
    public function setValidation(ValidationInterface $validation): static
    {
        $this->validation = $validation;

        return $this;
    }

    /**
     * Sets the default whitelist
     *
     * @phpstan-param forms_whitelist $whitelist
     */
    public function setWhitelist(array $whitelist): static
    {
        $this->whitelist = $whitelist;

        return $this;
    }

    /**
     * Check if the current element in the iterator is valid
     */
    public function valid(): bool
    {
        return isset($this->elementsIndexed[$this->position]);
    }
}
