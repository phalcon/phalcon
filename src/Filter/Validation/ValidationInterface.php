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
     * @param mixed              $field
     * @param ValidatorInterface $validator
     *
     * @return ValidationInterface
     */
    public function add(
        mixed $field,
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
     * @param mixed $entity
     * @param mixed $data
     *
     * @phpstan-param filter_validation_whitelist $whitelist
     *
     * @return ValidationInterface
     */
    public function bind(
        mixed $entity,
        mixed $data,
        array $whitelist = []
    ): ValidationInterface;

    /**
     * Returns the bound entity
     *
     * @return mixed
     */
    public function getEntity(): mixed;

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
     * @phpstan-return filter_validation_validators
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
     * @param mixed              $field
     * @param ValidatorInterface $validator
     *
     * @return ValidationInterface
     */
    public function rule(
        mixed $field,
        ValidatorInterface $validator
    ): ValidationInterface;

    /**
     * Adds the validators to a field
     *
     * @param string $field
     *
     * @phpstan-param filter_validators $validators
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
     * @param mixed        $filters
     *
     * @return ValidationInterface
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
     * @param mixed $data
     * @param mixed $entity
     *
     * @phpstan-param filter_validation_whitelist $whitelist
     *
     * @return false|Messages
     */
    public function validate(
        mixed $data = null,
        mixed $entity = null,
        array $whitelist = []
    ): false | Messages;
}
