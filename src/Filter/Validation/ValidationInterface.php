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

use Phalcon\Contracts\Filter\FilterTypes;
use Phalcon\Messages\MessageInterface;
use Phalcon\Messages\Messages;

/**
 * Interface for the Phalcon\Filter\Validation component
 *
 * @phpstan-import-type filter_validation_data from FilterTypes
 * @phpstan-import-type filter_validation_labels from FilterTypes
 * @phpstan-import-type filter_validation_validators from FilterTypes
 * @phpstan-import-type filter_validation_whitelist from FilterTypes
 * @phpstan-import-type filter_validators from FilterTypes
 */
interface ValidationInterface
{
    /**
     * Adds a validator to a field
     *
     * @param array|string       $field
     * @param ValidatorInterface $validator
     *
     * @phpstan-param mixed $field
     */
    public function add(
        mixed $field,
        ValidatorInterface $validator
    ): ValidationInterface;

    /**
     * Appends a message to the messages list
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
     * @phpstan-param mixed                       $entity
     * @phpstan-param filter_validation_data      $data
     * @phpstan-param filter_validation_whitelist $whitelist
     */
    public function bind(
        mixed $entity,
        mixed $data,
        array $whitelist = []
    ): ValidationInterface;

    /**
     * Returns the bound entity
     *
     * @return object
     *
     * @phpstan-return object|null
     */
    public function getEntity(): mixed;

    /**
     * Returns all the filters or a specific one
     */
    public function getFilters(string | null $field = null): mixed;

    /**
     * Get label for field
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
     * @phpstan-return filter_validation_validators
     */
    public function getValidators(): array;

    /**
     * Gets the a value to validate in the array/object data source
     */
    public function getValue(string $field): mixed;

    /**
     * Alias of `add` method
     *
     * @param array|string       $field
     * @param ValidatorInterface $validator
     *
     * @phpstan-param mixed $field
     */
    public function rule(
        mixed $field,
        ValidatorInterface $validator
    ): ValidationInterface;

    /**
     * Adds the validators to a field
     *
     * @phpstan-param filter_validators $validators
     */
    public function rules(
        string $field,
        array $validators
    ): ValidationInterface;

    /**
     * Adds filters to the field
     *
     * @param array|string $filters
     *
     * @phpstan-param mixed $filters
     */
    public function setFilters(
        string $field,
        mixed $filters
    ): ValidationInterface;

    /**
     * Adds labels for fields
     *
     * @phpstan-param filter_validation_labels $labels
     */
    public function setLabels(array $labels): void;

    /**
     * Validate a set of data according to a set of rules
     *
     * @param array|object $data
     * @param object       $entity
     *
     * @phpstan-param mixed $data
     * @phpstan-param filter_validation_whitelist $whitelist
     *
     * @return false|Messages
     */
    public function validate(
        mixed $data = null,
        mixed $entity = null,
        array $whitelist = []
    ): bool | Messages;
}
