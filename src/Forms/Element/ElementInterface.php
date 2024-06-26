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

use Phalcon\Filter\Validation\ValidatorInterface;
use Phalcon\Forms\Form;
use Phalcon\Messages\MessageInterface;
use Phalcon\Messages\Messages;

/**
 * Interface for Phalcon\Forms\Element classes
 */
interface ElementInterface
{
    /**
     * Adds a filter to current list of filters
     *
     * @param string $filter
     *
     * @return ElementInterface
     */
    public function addFilter(string $filter): ElementInterface;

    /**
     * Adds a validator to the element
     *
     * @param ValidatorInterface $validator
     *
     * @return ElementInterface
     */
    public function addValidator(
        ValidatorInterface $validator
    ): ElementInterface;

    /**
     * Adds a group of validators
     *
     * @param array $validators
     * @param bool  $merge
     *
     * @return ElementInterface
     */
    public function addValidators(
        array $validators,
        bool $merge = true
    ): ElementInterface;

    /**
     * Appends a message to the internal message list
     *
     * @param MessageInterface $message
     *
     * @return ElementInterface
     */
    public function appendMessage(MessageInterface $message): ElementInterface;

    /**
     * Clears every element in the form to its default value
     *
     * @return ElementInterface
     */
    public function clear(): ElementInterface;

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
    ): mixed;

    /**
     * Returns the default attributes for the element
     *
     * @return array
     */
    public function getAttributes(): array;

    /**
     * Returns the default value assigned to the element
     *
     * @return mixed
     */
    public function getDefault(): mixed;

    /**
     * Returns the element's filters
     *
     * @return array
     */
    public function getFilters(): array;

    /**
     * Returns the parent form to the element
     *
     * @return Form|null
     */
    public function getForm(): Form | null;

    /**
     * Returns the element's label
     *
     * @return string|null
     */
    public function getLabel(): string | null;

    /**
     * Returns the messages that belong to the element. The element needs to
     * be attached to a form
     *
     * @return Messages
     */
    public function getMessages(): Messages;

    /**
     * Returns the element's name
     *
     * @return string
     */
    public function getName(): string;

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
    ): mixed;

    /**
     * Returns the options for the element
     *
     * @return array
     */
    public function getUserOptions(): array;

    /**
     * Returns the validators registered for the element
     *
     * @return ValidatorInterface[]
     */
    public function getValidators(): array;

    /**
     * Returns the element's value
     *
     * @return mixed
     */
    public function getValue(): mixed;

    /**
     * Checks whether there are messages attached to the element
     *
     * @return bool
     */
    public function hasMessages(): bool;

    /**
     * Generate the HTML to label the element
     *
     * @param array $attributes
     *
     * @return string
     */
    public function label(array $attributes = []): string;

    /**
     * Renders the element widget
     *
     * @param array $attributes
     *
     * @return string
     */
    public function render(array $attributes = []): string;

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
    ): ElementInterface;

    /**
     * Sets default attributes for the element
     *
     * @param array $attributes
     *
     * @return ElementInterface
     */
    public function setAttributes(array $attributes): ElementInterface;

    /**
     * Sets a default value in case the form does not use an entity
     * or there is no value available for the element in _POST
     *
     * @param mixed $value
     *
     * @return ElementInterface
     */
    public function setDefault(mixed $value): ElementInterface;

    /**
     * Sets the element's filters
     *
     * @param array|string $filters
     *
     * @return ElementInterface
     */
    public function setFilters(array | string $filters): ElementInterface;

    /**
     * Sets the parent form to the element
     *
     * @param Form $form
     *
     * @return ElementInterface
     */
    public function setForm(Form $form): ElementInterface;

    /**
     * Sets the element label
     *
     * @param string $label
     *
     * @return ElementInterface
     */
    public function setLabel(string $label): ElementInterface;

    /**
     * Sets the validation messages related to the element
     *
     * @param Messages $messages
     *
     * @return ElementInterface
     */
    public function setMessages(Messages $messages): ElementInterface;

    /**
     * Sets the element's name
     *
     * @param string $name
     *
     * @return ElementInterface
     */
    public function setName(string $name): ElementInterface;

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
    ): ElementInterface;

    /**
     * Sets options for the element
     *
     * @param array $options
     *
     * @return ElementInterface
     */
    public function setUserOptions(array $options): ElementInterface;
}
