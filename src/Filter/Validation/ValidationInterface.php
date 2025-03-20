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

namespace Phalcon\Filter\Validation;

use Phalcon\Messages\MessageInterface;
use Phalcon\Messages\Messages;

/**
 * Interface for the Phalcon\Filter\Validation component
 */
interface ValidationInterface
{
    /**
     * Adds a validator to a field
     *
     * @param array|string       $field
     * @param ValidatorInterface $validator
     *
     * @return ValidationInterface
     */
    public function add(
        array | string $field,
        ValidatorInterface $validator
    ): ValidationInterface;

    /**
     * Appends a message to the messages list
     *
     * @param MessageInterface $message
     *
     * @return ValidationInterface
     */
    public function appendMessage(
        MessageInterface $message
    ): ValidationInterface;

    /**
     * Assigns the data to an entity
     * The entity is used to obtain the validation values
     *
     * @param object       $entity
     * @param array|object $data
     *
     * @return ValidationInterface
     */
    public function bind(
        object $entity,
        array | object $data
    ): ValidationInterface;

    /**
     * Returns the bound entity
     *
     * @return object|null
     */
    public function getEntity(): object | null;

    /**
     * Returns all the filters or a specific one
     *
     * @param string|null $field
     *
     * @return mixed
     */
    public function getFilters(string | null $field = null): mixed;

    /**
     * Get label for field
     *
     * @param string $field
     *
     * @return string
     */
    public function getLabel(string $field): string;

    /**
     * Returns the registered validators
     *
     * @return Messages
     */
    public function getMessages(): Messages;

    /**
     * Returns the validators added to the validation
     *
     * @return array
     */
    public function getValidators(): array;

    /**
     * Gets the value to validate in the array/object data source
     *
     * @param string $field
     *
     * @return mixed
     */
    public function getValue(string $field): mixed;

    /**
     * Alias of `add` method
     *
     * @param array|string       $field
     * @param ValidatorInterface $validator
     *
     * @return ValidationInterface
     * @todo remove this
     */
    public function rule(
        array | string $field,
        ValidatorInterface $validator
    ): ValidationInterface;

    /**
     * Adds the validators to a field
     *
     * @param string $field
     * @param array  $validators
     *
     * @return ValidationInterface
     */
    public function rules(
        string $field,
        array $validators
    ): ValidationInterface;

    /**
     * Adds filters to the field
     *
     * @param string       $field
     * @param array|string $filters
     *
     * @return ValidationInterface
     */
    public function setFilters(
        string $field,
        array | string $filters
    ): ValidationInterface;

    /**
     * Adds labels for fields
     *
     * @param array $labels
     *
     * @return ValidationInterface
     */
    public function setLabels(array $labels): ValidationInterface;

    /**
     * Validate a set of data according to a set of rules
     *
     * @param array|object|null $data
     * @param object|null       $entity
     *
     * @return Messages|false
     */
    public function validate(
        array | object | null $data = null,
        object | null $entity = null
    ): Messages | false;
}
