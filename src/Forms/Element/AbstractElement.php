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
use Phalcon\Di\Di;
use Phalcon\Di\DiInterface;
use Phalcon\Filter\Validation\ValidatorInterface;
use Phalcon\Forms\Exception;
use Phalcon\Forms\Exceptions\FormElementNameRequired;
use Phalcon\Forms\Exceptions\InvalidFilterType;
use Phalcon\Forms\Form;
use Phalcon\Html\TagFactory;
use Phalcon\Messages\MessageInterface;
use Phalcon\Messages\Messages;
use Stringable;

/**
 * This is a base class for form elements
 *
 * @phpstan-import-type forms_attributes from FormsTypes
 * @phpstan-import-type forms_filters from FormsTypes
 * @phpstan-import-type forms_options from FormsTypes
 * @phpstan-import-type forms_validators from FormsTypes
 * @phpstan-import-type html_attributes from HtmlTypes
 */
abstract class AbstractElement implements ElementInterface
{
    /**
     * @var forms_attributes
     */
    protected array $attributes = [];

    /**
     * @var forms_filters
     */
    protected array $filters = [];
    protected Form | null $form = null;
    protected string | null $label = null;
    protected Messages $messages;
    protected string $method = "inputText";
    protected string $name;
    /**
     * @var forms_options
     */
    protected array $options = [];
    protected TagFactory | null $tagFactory = null;
    /**
     * @var forms_validators
     */
    protected array $validators = [];

    /**
     * @var mixed|null
     */
    protected mixed $value = null;

    /**
     * Constructor
     *
     * @phpstan-param forms_attributes $attributes
     */
    public function __construct(string $name, array $attributes = [])
    {
        $name = trim($name);

        if (empty($name)) {
            throw new FormElementNameRequired();
        }

        $this->name       = $name;
        $this->attributes = $attributes;
        $this->messages   = new Messages();
    }

    /**
     * Magic method __toString renders the widget without attributes
     */
    public function __toString(): string
    {
        return $this->render();
    }

    /**
     * Adds a filter to current list of filters
     */
    public function addFilter(string $filter): ElementInterface
    {
        $this->filters[] = $filter;

        return $this;
    }

    /**
     * Adds a validator to the element
     */
    public function addValidator(
        ValidatorInterface $validator
    ): ElementInterface {
        $this->validators[] = $validator;

        return $this;
    }

    /**
     * Adds a group of validators
     *
     * @phpstan-param array<array-key, mixed> $validators
     */
    public function addValidators(
        array $validators,
        bool $merge = true
    ): ElementInterface {
        if (true !== $merge) {
            $this->validators = [];
        }

        foreach ($validators as $validator) {
            if ($validator instanceof ValidatorInterface) {
                $this->addValidator($validator);
            }
        }

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
        $this->form?->clear($this->name);

        return $this;
    }

    /**
     * Returns the value of an attribute if present
     */
    public function getAttribute(
        string $attribute,
        mixed $defaultValue = null
    ): mixed {
        return $this->attributes[$attribute] ?? $defaultValue;
    }

    /**
     * Returns the default attributes for the element
     *
     * @phpstan-return forms_attributes
     */
    public function getAttributes(): array
    {
        return $this->attributes;
    }

    /**
     * Returns the default value assigned to the element
     */
    public function getDefault(): mixed
    {
        return $this->value;
    }

    /**
     * Returns the element filters
     *
     * @phpstan-return forms_filters
     */
    public function getFilters(): array
    {
        return $this->filters;
    }

    /**
     * Returns the parent form to the element
     */
    public function getForm(): Form | null
    {
        return $this->form;
    }

    /**
     * Returns the element label
     */
    public function getLabel(): string | null
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
     * Returns the tagFactory; throws exception if not present
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
     * Returns the validators registered for the element
     *
     * @phpstan-return forms_validators
     */
    public function getValidators(): array
    {
        return $this->validators;
    }

    /**
     * Returns the element's value
     */
    public function getValue(): mixed
    {
        /**
         * If element belongs to the form, get value from the form
         */
        if (null !== $this->form) {
            return $this->form->getValue($this->name);
        }

        /**
         * Assign the default value if there is no form available
         */
        return $this->value;
    }

    /**
     * Checks whether there are messages attached to the element
     */
    public function hasMessages(): bool
    {
        return $this->messages->count() > 0;
    }

    /**
     * Generate the HTML to label the element
     *
     * @phpstan-param html_attributes $attributes
     */
    public function label(array $attributes = []): string
    {
        /**
         * Check if there is an "id" attribute defined
         */
        $tagFactory = $this->getLocalTagFactory();
        /** @var string $name */
        $name       = $this->attributes["id"] ?? $this->name;

        if (!isset($attributes["for"])) {
            $attributes["for"] = $name;
        }

        /**
         * Use the default label or leave the same name as label
         */
        $labelName = $this->label;

        if (!($labelName || is_numeric($labelName))) {
            $labelName = $name;
        }

        return $tagFactory->label($labelName, $attributes);
    }

    /**
     * Renders the element widget returning HTML
     *
     * @phpstan-param html_attributes $attributes
     */
    public function render(array $attributes = []): string
    {
        /** @var scalar|null $value */
        $value      = $this->getValue();
        $method     = $this->method;
        $tagFactory = $this->getLocalTagFactory();

        if (isset($attributes["value"])) {
            $value = $attributes["value"];
            unset($attributes["value"]);
        }

        if (null !== $value) {
            $value = (string)$value;
        }

        $merged = array_merge($this->attributes, $attributes);

        /** @var string|Stringable $result */
        $result = $tagFactory->{$method}($this->name, $value, $merged);

        return (string)$result;
    }

    /**
     * Sets a default attribute for the element
     */
    public function setAttribute(
        string $attribute,
        mixed $value
    ): ElementInterface {
        $this->attributes[$attribute] = $value;

        return $this;
    }

    /**
     * Sets default attributes for the element
     *
     * @phpstan-param forms_attributes $attributes
     */
    public function setAttributes(array $attributes): ElementInterface
    {
        $this->attributes = $attributes;

        return $this;
    }

    /**
     * Sets a default value in case the form does not use an entity
     * or there is no value available for the element in _POST
     */
    public function setDefault(mixed $value): ElementInterface
    {
        $this->value = $value;

        return $this;
    }

    /**
     * Sets the element filters
     *
     * @phpstan-param forms_filters|string $filters
     */
    public function setFilters(
        array | string $filters
    ): ElementInterface {
        if (!is_string($filters) && !is_array($filters)) {
            throw new InvalidFilterType();
        }

        if (is_string($filters)) {
            $filters = [$filters];
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
    public function setName(string $name): ElementInterface
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Sets the TagFactory
     */
    public function setTagFactory(TagFactory $tagFactory): static
    {
        $this->tagFactory = $tagFactory;

        return $this;
    }

    /**
     * Sets an option for the element
     */
    public function setUserOption(
        string $option,
        mixed $value
    ): ElementInterface {
        $this->options[$option] = $value;

        return $this;
    }

    /**
     * Sets options for the element
     *
     * @phpstan-param forms_options $options
     */
    public function setUserOptions(array $options): ElementInterface
    {
        $this->options = $options;

        return $this;
    }

    /**
     * Returns the tagFactory; throws exception if not present
     */
    protected function getLocalTagFactory(): TagFactory
    {
        if (null === $this->tagFactory) {
            /**
             * Check the form for the TagFactory
             */
            $tagFactory = $this->form?->getTagFactory();

            /**
             * Check the DI container
             */
            if (null === $tagFactory) {
                /** @var DiInterface|null $container */
                $container = Di::getDefault();

                if (null !== $container && true === $container->has("tag")) {
                    /** @var TagFactory $tagFactory */
                    $tagFactory = $container->getShared("tag");
                }
            }

            if (null === $tagFactory) {
                throw Exception::tagFactoryNotFound();
            }

            $this->tagFactory = $tagFactory;
        }

        return $this->tagFactory;
    }
}
