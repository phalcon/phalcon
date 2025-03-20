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
use Phalcon\Di\Di;
use Phalcon\Filter\Validation\ValidatorInterface;
use Phalcon\Forms\Form;
use Phalcon\Html\Escaper;
use Phalcon\Html\Escaper\EscaperInterface;
use Phalcon\Html\TagFactory;
use Phalcon\Messages\MessageInterface;
use Phalcon\Messages\Messages;

/**
 * This is a base class for form elements
 */
abstract class AbstractElement implements ElementInterface
{
    /**
     * @var array
     */
    protected array $attributes = [];

    /**
     * @var array
     */
    protected array $filters = [];

    /**
     * @var Form|null
     */
    protected Form | null $form = null;

    /**
     * @var string|null
     */
    protected string | null $label = null;
    /**
     * @var Messages
     */
    protected Messages $messages;
    /**
     * @var string
     */
    protected string $method = "inputText";
    /**
     * @var string
     */
    protected string $name;

    /**
     * @var array
     */
    protected array $options = [];

    /**
     * @var TagFactory|null
     */
    protected TagFactory | null $tagFactory = null;

    /**
     * @var array
     */
    protected array $validators = [];

    /**
     * @var mixed|null
     */
    protected mixed $value = null;

    /**
     * Constructor
     *
     * @param string $name       Attribute name (value of 'name' attribute of HTML element)
     * @param array  $attributes Additional HTML element attributes
     */
    public function __construct(string $name, array $attributes = [])
    {
        $name = trim($name);

        if (empty($name)) {
            throw new InvalidArgumentException(
                "Form element name is required"
            );
        }

        $this->name       = $name;
        $this->attributes = $attributes;
        $this->messages   = new Messages();
    }

    /**
     * Magic method __toString renders the widget without attributes
     *
     * @return string
     */
    public function __toString(): string
    {
        return $this->render();
    }

    /**
     * Adds a filter to current list of filters
     *
     * @param string $filter
     *
     * @return ElementInterface
     */
    public function addFilter(string $filter): ElementInterface
    {
        $this->filters[] = $filter;

        return $this;
    }

    /**
     * Adds a validator to the element
     *
     * @param ValidatorInterface $validator
     *
     * @return ElementInterface
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
     * @param ValidatorInterface[] $validators
     * @param bool                 $merge
     *
     * @return ElementInterface
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
     *
     * @param MessageInterface $message
     *
     * @return ElementInterface
     */
    public function appendMessage(MessageInterface $message): ElementInterface
    {
        $this->messages->appendMessage($message);

        return $this;
    }

    /**
     * Clears element to its default value
     *
     * @return ElementInterface
     */
    public function clear(): ElementInterface
    {
        $this->form?->clear($this->name);

        return $this;
    }

    /**
     * Returns the value of an attribute if present
     *
     * @param string     $attribute
     * @param mixed|null $defaultValue
     *
     * @return mixed
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
     * @return array
     */
    public function getAttributes(): array
    {
        return $this->attributes;
    }

    /**
     * Returns the default value assigned to the element
     *
     * @return mixed
     */
    public function getDefault(): mixed
    {
        return $this->value;
    }

    /**
     * Returns the element filters
     *
     * @return array
     */
    public function getFilters(): array
    {
        return $this->filters;
    }

    /**
     * Returns the parent form to the element
     *
     * @return Form|null
     */
    public function getForm(): Form | null
    {
        return $this->form;
    }

    /**
     * Returns the element label
     *
     * @return string | null
     */
    public function getLabel(): string | null
    {
        return $this->label;
    }

    /**
     * Returns the messages that belong to the element. The element needs to
     * be attached to a form
     *
     * @return Messages
     */
    public function getMessages(): Messages
    {
        return $this->messages;
    }

    /**
     * Returns the element name
     *
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * Returns the tagFactory; throws exception if not present
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
     * Returns the validators registered for the element
     *
     * @return ValidatorInterface[]
     */
    public function getValidators(): array
    {
        return $this->validators;
    }

    /**
     * Returns the element's value
     *
     * @return mixed
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
     *
     * @return bool
     */
    public function hasMessages(): bool
    {
        return $this->messages->count() > 0;
    }

    /**
     * Generate the HTML to label the element
     *
     * @param array $attributes
     *
     * @return string
     */
    public function label(array $attributes = []): string
    {
        /**
         * Check if there is an "id" attribute defined
         */
        $tagFactory = $this->getLocalTagFactory();
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
     * @param array $attributes
     *
     * @return string
     */
    public function render(array $attributes = []): string
    {
        $value      = $this->getValue();
        $method     = $this->method;
        $tagFactory = $this->getLocalTagFactory();

        if (isset($attributes["value"])) {
            $value = $attributes["value"];
            unset($attributes["value"]);
        }

        $merged = array_merge($this->attributes, $attributes);

        return (string)$tagFactory->{$method}($this->name, $value, $merged);
    }

    /**
     * Sets a default attribute for the element
     *
     * @param string $attribute
     * @param mixed  $value
     *
     * @return ElementInterface
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
     * @param array $attributes
     *
     * @return ElementInterface
     */
    public function setAttributes(array $attributes): ElementInterface
    {
        $this->attributes = $attributes;

        return $this;
    }

    /**
     * Sets a default value in case the form does not use an entity
     * or there is no value available for the element in _POST
     *
     * @param mixed $value
     *
     * @return ElementInterface
     */
    public function setDefault(mixed $value): ElementInterface
    {
        $this->value = $value;

        return $this;
    }

    /**
     * Sets the element filters
     *
     * @param array|string $filters
     *
     * @return ElementInterface
     */
    public function setFilters(
        array | string $filters
    ): ElementInterface {
        if (is_string($filters)) {
            $filters = [$filters];
        }

        $this->filters = $filters;

        return $this;
    }

    /**
     * Sets the parent form to the element
     *
     * @param Form $form
     *
     * @return ElementInterface
     */
    public function setForm(Form $form): ElementInterface
    {
        $this->form = $form;

        return $this;
    }

    /**
     * Sets the element label
     *
     * @param string $label
     *
     * @return ElementInterface
     */
    public function setLabel(string $label): ElementInterface
    {
        $this->label = $label;

        return $this;
    }

    /**
     * Sets the validation messages related to the element
     *
     * @param Messages $messages
     *
     * @return ElementInterface
     */
    public function setMessages(Messages $messages): ElementInterface
    {
        $this->messages = $messages;

        return $this;
    }

    /**
     * Sets the element name
     *
     * @param string $name
     *
     * @return ElementInterface
     */
    public function setName(string $name): ElementInterface
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Sets the TagFactory
     *
     * @param TagFactory $tagFactory
     *
     * @return $this
     */
    public function setTagFactory(TagFactory $tagFactory): AbstractElement
    {
        $this->tagFactory = $tagFactory;

        return $this;
    }

    /**
     * Sets an option for the element
     *
     * @param string $option
     * @param mixed  $value
     *
     * @return ElementInterface
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
     * @param array $options
     *
     * @return ElementInterface
     */
    public function setUserOptions(array $options): ElementInterface
    {
        $this->options = $options;

        return $this;
    }

    /**
     * Returns the tagFactory; throws exception if not present
     *
     * @return TagFactory
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
                $container = Di::getDefault();

                if (true === $container->has("tag")) {
                    $tagFactory = $container->getShared("tag");
                }
            }

            /**
             * All failed, create a new TagFactory
             */
            if (null === $tagFactory) {
                $container = Di::getDefault();
                $escaper   = $container->getShared("escaper");
                if (!($escaper instanceof EscaperInterface)) {
                    $escaper = new Escaper();
                }

                $tagFactory = new TagFactory($escaper);
            }

            $this->tagFactory = $tagFactory;
        }

        return $this->tagFactory;
    }
}
