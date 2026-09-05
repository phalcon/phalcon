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

namespace Phalcon\Filter;

use Phalcon\Contracts\Filter\FilterTypes;
use Phalcon\Di\Di;
use Phalcon\Di\Exception as DiException;
use Phalcon\Di\Injectable;
use Phalcon\Filter\Validation\AbstractCombinedFieldsValidator;
use Phalcon\Filter\Validation\Exception as ValidationException;
use Phalcon\Filter\Validation\Exceptions\FilterServiceUnavailable;
use Phalcon\Filter\Validation\Exceptions\InvalidFieldType;
use Phalcon\Filter\Validation\Exceptions\InvalidFilterService;
use Phalcon\Filter\Validation\Exceptions\InvalidValidationData;
use Phalcon\Filter\Validation\Exceptions\InvalidValidator;
use Phalcon\Filter\Validation\Exceptions\InvalidValidatorScope;
use Phalcon\Filter\Validation\Exceptions\NoDataToValidate;
use Phalcon\Filter\Validation\Exceptions\ValidationEntityNotObject;
use Phalcon\Filter\Validation\ValidationInterface;
use Phalcon\Filter\Validation\ValidatorInterface;
use Phalcon\Messages\MessageInterface;
use Phalcon\Messages\Messages;
use Phalcon\Traits\Support\Helper\Str\CamelizeTrait;

use function array_filter;
use function array_merge;
use function in_array;
use function is_array;
use function is_object;
use function is_string;
use function method_exists;
use function property_exists;

/**
 * Allows to validate data using custom or built-in validators
 *
 * @phpstan-import-type filter_sanitizers from FilterTypes
 * @phpstan-import-type filter_validation_combined_validators from FilterTypes
 * @phpstan-import-type filter_validation_data from FilterTypes
 * @phpstan-import-type filter_validation_default_messages from FilterTypes
 * @phpstan-import-type filter_validation_filters from FilterTypes
 * @phpstan-import-type filter_validation_labels from FilterTypes
 * @phpstan-import-type filter_validation_validators from FilterTypes
 * @phpstan-import-type filter_validation_values from FilterTypes
 * @phpstan-import-type filter_validation_whitelist from FilterTypes
 * @phpstan-import-type filter_validators from FilterTypes
 */
class Validation extends Injectable implements ValidationInterface
{
    use CamelizeTrait;

    /**
     * Default messages for validators, keyed by validator class name
     *
     * Declared without an array initializer on purpose: an initialized static
     * array makes Zephir emit a zephir_init_static_properties() function that
     * fails to compile in the single-file build. It is null until first set
     * and treated as an empty array by the accessors below.
     *
     * @phpstan-var filter_validation_default_messages
     */
    protected static array $defaultMessages = [];

    /**
     * @phpstan-var filter_validation_combined_validators
     */
    protected array $combinedFieldsValidators = [];

    /**
     * @phpstan-var filter_validation_data
     */
    protected mixed $data = null;

    /**
     * @var object|null
     */
    protected object | null $entity = null;

    /**
     * @phpstan-var filter_validation_filters
     */
    protected array $filters = [];

    /**
     * @phpstan-var filter_validation_labels
     */
    protected array $labels = [];

    /**
     * @var Messages
     */
    protected Messages $messages;

    /**
     * List of validators
     *
     * @phpstan-var filter_validation_validators
     */
    protected array $validators = [];

    /**
     * Calculated values
     *
     * @phpstan-var filter_validation_values
     */
    protected array $values = [];

    /**
     * @phpstan-var filter_validation_whitelist
     */
    protected array $whitelist = [];

    /**
     * Phalcon\Filter\Validation constructor
     *
     * @phpstan-param filter_validation_validators $validators
     */
    public function __construct(array $validators = [])
    {
        $this->messages   = new Messages();
        $this->validators = array_filter(
            $validators,
            function (array $element) {
                return !is_array($element[0]) ||
                    !($element[1] instanceof AbstractCombinedFieldsValidator);
            }
        );

        $this->combinedFieldsValidators = array_filter(
            $validators,
            function (array $element) {
                return is_array($element[0]) &&
                    $element[1] instanceof AbstractCombinedFieldsValidator;
            }
        );

        /**
         * Check for an 'initialize' method
         */
        if (true === method_exists($this, "initialize")) {
            $this->initialize();
        }
    }

    /**
     * Returns the default message registered for a validator class, or an
     * empty string when none has been registered.
     *
     * @param string $validatorClassName
     *
     * @return string
     */
    public static function getDefaultMessage(string $validatorClassName): string
    {
        return self::$defaultMessages[$validatorClassName] ?? "";
    }

    /**
     * Registers default messages for validators, keyed by validator class
     * name. A registered default is used when a validator does not define its
     * own message; a message set on the validator instance still wins. Calls
     * are merged, so defaults can be registered incrementally.
     *
     * @phpstan-param filter_validation_default_messages $messages
     *
     * @phpstan-return filter_validation_default_messages
     */
    public static function setDefaultMessages(array $messages = []): array
    {
        self::$defaultMessages = array_merge(self::$defaultMessages, $messages);

        return self::$defaultMessages;
    }

    /**
     * Adds a validator to a field
     *
     * @param array|string       $field
     * @param ValidatorInterface $validator
     *
     * @phpstan-param mixed $field
     *
     * @phpstan-return static
     * @throws ValidationException
     */
    public function add(
        mixed $field,
        ValidatorInterface $validator
    ): static {
        if (is_array($field)) {
            /**
             * A field list holds field names. Uniqueness validator for
             * combination of fields is handled differently
             *
             * @var array<array-key, string> $field
             */
            if ($validator instanceof AbstractCombinedFieldsValidator) {
                $this->combinedFieldsValidators[] = [$field, $validator];
            } else {
                foreach ($field as $singleField) {
                    $this->validators[$singleField][] = $validator;
                }
            }
        } elseif (is_string($field)) {
            $this->validators[$field][] = $validator;
        } else {
            throw new InvalidFieldType();
        }

        return $this;
    }

    /**
     * Appends a message to the messages list
     */
    public function appendMessage(
        MessageInterface $message
    ): static {
        $this->messages->appendMessage($message);

        return $this;
    }

    /**
     * Assigns the data to an entity
     * The entity is used to obtain the validation values
     *
     * ```php
     * $entity = new Author();
     * $fields = ['name', 'email', 'imageUrl'];
     * $validation = new AuthorValidation();
     * $validation->bind($entity, $_POST, $fields);
     * $validation->validate();
     * ```
     *
     * @param object $entity
     *
     * @phpstan-param mixed                       $entity
     * @phpstan-param filter_validation_data      $data
     * @phpstan-param filter_validation_whitelist $whitelist
     */
    public function bind(
        mixed $entity,
        mixed $data,
        array $whitelist = []
    ): static {
        $this->data = $data;
        $this->setEntity($entity);

        // if data is not an array / object, entity is null, or data is empty, then no need to proceed further
        if (
            (gettype($data) != "array" && gettype($data) != "object") ||
            null === $entity ||
            empty($data)
        ) {
            return $this;
        }

        /**
         * `setEntity()` throws unless the value is an object, so the local
         * holds the same object as the property from here on.
         *
         * @phpstan-var object $entity
         */
        $container = $this->getDI();

        if (null === $container) {
            $container = Di::getDefault();

            if (null === $container) {
                throw new FilterServiceUnavailable();
            }
        }

        $filterService = $container->getShared("filter");

        if (!is_object($filterService)) {
            throw new InvalidFilterService();
        }

        /**
         * Mirrors the `<FilterInterface>` cast in the Zephir source.
         *
         * @var FilterInterface $filterService
         */
        if (empty($whitelist)) {
            $whitelist = $this->whitelist;
        }

        foreach ($data as $field => $value) {
            /**
             * Skip numeric (integer) keys; entity setters and properties are
             * always string-named, so camelize() would fail on them. See
             * cphalcon issue #17173.
             */
            if (!is_string($field)) {
                continue;
            }

            /**
             * Check if the field is in the whitelist
             */
            if (!empty($whitelist) && !in_array($field, $whitelist)) {
                continue;
            }

            if (isset($this->filters[$field])) {
                /** @phpstan-var filter_sanitizers|string $fieldFilters */
                $fieldFilters = $this->filters[$field];

                $value = $filterService->sanitize($value, $fieldFilters);
            }

            /**
             * Set value in entity
             */
            $method = "set" . $this->toCamelize($field);

            if (method_exists($entity, $method)) {
                $entity->{$method}($value);
            } elseif (method_exists($entity, "writeAttribute")) {
                $entity->writeAttribute($field, $value);
            } elseif (property_exists($entity, $field)) {
                $entity->{$field} = $value;
            }
        }

        return $this;
    }

    /**
     * Verify if validation fails by verifying if there are messages in the current validation
     */
    public function fails(): bool
    {
        if ($this->messages->count() > 0) {
            return true;
        }

        return false;
    }

    /**
     * @return mixed
     */
    public function getData(): mixed
    {
        return $this->data;
    }

    /**
     * Returns the bound entity
     *
     * @return object
     *
     * @phpstan-return object|null
     */
    public function getEntity(): mixed
    {
        return $this->entity;
    }

    /**
     * Returns all the filters or a specific one
     */
    public function getFilters(string | null $field = null): mixed
    {
        if (empty($field)) {
            return $this->filters;
        }

        return $this->filters[$field] ?? null;
    }

    /**
     * Get label for field
     *
     * @param array|string $field
     *
     * @phpstan-param mixed $field
     */
    public function getLabel(mixed $field): string
    {
        if (is_array($field)) {
            /** @phpstan-var array<array-key, string> $field */
            return implode(", ", $field);
        }

        /**
         * A single field is a field name.
         *
         * @var string $field
         */
        return $this->labels[$field] ?? $field;
    }

    /**
     * Returns the registered validators
     */
    public function getMessages(): Messages
    {
        return $this->messages;
    }

    /**
     * Returns the validators added to the validation
     *
     * @phpstan-return filter_validation_validators
     */
    public function getValidators(): array
    {
        return $this->validators;
    }

    /**
     * Gets the value to validate in the array/object data source
     *
     * @param string $field
     *
     * @return mixed
     * @throws ValidationException
     * @throws DiException
     */
    public function getValue(string $field): mixed
    {
        $isRawFetched = false;

        //  If the entity is an object use it to retrieve the values
        if (null !== $this->entity) {
            $value = $this->getValueByEntity($this->entity, $field);
            if (null === $value) {
                $isRawFetched = true;
                $value        = $this->getValueByData($this->data, $field);
            }
        } else {
            if (
                gettype($this->data) != "array" &&
                gettype($this->data) != "object"
            ) {
                throw new NoDataToValidate();
            }

            $value = $this->getValueByData($this->data, $field);
        }

        if (null === $value) {
            return null;
        }

        if (
            isset($this->filters[$field]) &&
            !empty($this->filters[$field])
        ) {
            $fieldFilters = $this->filters[$field];
            $container    = $this->getDI();

            if (null === $container) {
                $container = Di::getDefault();

                if (null === $container) {
                    throw new FilterServiceUnavailable();
                }
            }

            $filterService = $container->getShared("filter");

            if (!is_object($filterService)) {
                throw new InvalidFilterService();
            }

            /**
             * Mirrors the `<FilterInterface>` cast in the Zephir source.
             *
             * @var FilterInterface $filterService
             *
             * @phpstan-var filter_sanitizers|string $fieldFilters
             */
            $value = $filterService->sanitize($value, $fieldFilters);

            /**
             * Set filtered value in entity
             */
            if (null !== $this->entity && false === $isRawFetched) {
                $method = "set" . $this->toCamelize($field);

                if (true === method_exists($this->entity, $method)) {
                    $this->entity->{$method}($value);
                } elseif (true === method_exists($this->entity, "writeAttribute")) {
                    $this->entity->writeAttribute($field, $value);
                } elseif (true === property_exists($this->entity, $field)) {
                    $this->entity->{$field} = $value;
                }
            }

            return $value;
        }

        // Cache the calculated value only if it's not entity
        if (null === $this->entity) {
            $this->values[$field] = $value;
        }

        return $value;
    }

    /**
     * Gets the value to validate in the array/object data source
     */
    public function getValueByData(
        mixed $data,
        string $field
    ): mixed {
        if (isset($this->values[$field])) {
            return $this->values[$field];
        }

        if (is_array($data) && isset($data[$field])) {
            return $data[$field];
        }

        return $data->{$field} ?? null;
    }

    /**
     * Gets the value to validate in the object entity source
     */
    public function getValueByEntity(
        mixed $entity,
        string $field
    ): mixed {
        /**
         * The entity is only read when `getValue()` has one, so it is an
         * object here. The parameter is mixed to match the untyped Zephir
         * signature.
         *
         * @var object $entity
         */
        $method = "get" . $this->toCamelize($field);

        if (true === method_exists($entity, $method)) {
            return $entity->{$method}();
        }

        if (true === method_exists($entity, "readAttribute")) {
            return $entity->readAttribute($field);
        }

        return $entity->{$field} ?? null;
    }

    /**
     * Alias of `add` method
     *
     * @param array|string $field
     *
     * @phpstan-param mixed $field
     *
     * @todo remove this
     */
    public function rule(
        mixed $field,
        ValidatorInterface $validator
    ): static {
        return $this->add($field, $validator);
    }

    /**
     * Adds the validators to a field
     *
     * @phpstan-param filter_validators $validators
     */
    public function rules(
        mixed $field,
        array $validators
    ): static {
        foreach ($validators as $validator) {
            if ($validator instanceof ValidatorInterface) {
                $this->add($field, $validator);
            }
        }

        return $this;
    }

    /**
     * Sets the bound entity
     *
     * @param mixed $entity
     *
     * @return void
     */
    public function setEntity(mixed $entity): void
    {
        if (!is_object($entity)) {
            throw new ValidationEntityNotObject();
        }

        $this->entity = $entity;
    }

    /**
     * Adds filters to the field
     *
     * @param array|string $field
     * @param array|string $filters
     *
     * @phpstan-param mixed $field
     * @phpstan-param mixed $filters
     */
    public function setFilters(
        mixed $field,
        mixed $filters
    ): static {
        if (is_array($field)) {
            /**
             * A field list holds field names.
             *
             * @var array<array-key, string> $field
             */
            foreach ($field as $singleField) {
                $this->filters[$singleField] = $filters;
            }
        } elseif (is_string($field)) {
            $this->filters[$field] = $filters;
        } else {
            throw new InvalidFieldType();
        }

        return $this;
    }

    /**
     * Adds labels for fields
     *
     * @phpstan-param filter_validation_labels $labels
     */
    public function setLabels(array $labels): void
    {
        $this->labels = $labels;
    }

    /**
     * Sets the validator array
     *
     * @phpstan-param filter_validation_validators $validators
     */
    public function setValidators(array $validators): static
    {
        $this->validators = $validators;

        return $this;
    }

    /**
     * Validate a set of data according to a set of rules
     *
     * You can use $validation->bind(entity, data, whitelist)->validate()
     * When you use bind(), the this->data is already set, so you can reuse it here
     *
     * ```php
     * // using bind() with $whitelist fields
     * $entity = new Author();
     * $fields = ['name', 'email', 'imageUrl'];
     * $validation = new AuthorValidation();
     * $validation->bind($entity, $_POST, $fields);
     * $validation->validate();
     *
     * // directly using validate
     * $validation = new AuthorValidation();
     * $validation->validate($_POST, $entity, $fields);
     * ```
     *
     * @param array|object $data
     *
     * @phpstan-param mixed $data
     * @phpstan-param object $entity
     * @phpstan-param filter_validation_whitelist $whitelist
     *
     * @return false|Messages
     */
    public function validate(
        mixed $data = null,
        mixed $entity = null,
        array $whitelist = []
    ): false | Messages {
        /**
         * Clear pre-calculated values
         */
        $this->values = [];

        /**
         * Implicitly creates a Phalcon\Messages\Messages object
         */
        $this->messages = new Messages();

        if (null !== $data) {
            // if data is provided
            if (
                gettype($data) != "array" &&
                gettype($data) != "object"
            ) {
                throw new InvalidValidationData();
            }

            $this->data = $data;
        } elseif (!empty($this->data)) {
            // else, if data === null, but we have this->data from bind(), reuse this->data
            $data = $this->data;
        }

        if (null !== $entity) {
            // if user provided entity, bind and assign the data to the entity
            $this->bind($entity, $data, $whitelist);
        }

        /**
         * Validation classes can implement the 'beforeValidation' callback
         */
        if (
            true === method_exists($this, "beforeValidation") &&
            false === $this->beforeValidation(
                $data,
                $this->entity,
                $this->messages
            )
        ) {
            return false;
        }

        foreach ($this->validators as $field => $validators) {
            /**
             * Each entry holds the validators registered for the field.
             *
             * @var iterable<array-key, mixed> $validators
             */
            foreach ($validators as $validator) {
                if (!is_object($validator)) {
                    throw new InvalidValidator();
                }

                /**
                 * Call internal validations, if it returns true, then skip the
                 * current validator
                 *
                 * @phpstan-var ValidatorInterface $validator
                 */
                if (true === $this->preChecking($field, $validator)) {
                    continue;
                }

                /**
                 * Check if the validation must be canceled if this validator fails
                 */
                if (
                    false === $validator->validate($this, $field) &&
                    $validator->getOption("cancelOnFail")
                ) {
                    break;
                }
            }
        }

        foreach ($this->combinedFieldsValidators as $scope) {
            if (!is_array($scope)) {
                throw new InvalidValidatorScope();
            }

            $field     = $scope[0];
            $validator = $scope[1];

            if (!is_object($validator)) {
                throw new InvalidValidator();
            }

            /**
             * Call internal validations, if it returns true, then skip the
             * current validator
             *
             * @phpstan-var ValidatorInterface $validator
             */
            if (true === $this->preChecking($field, $validator)) {
                continue;
            }

            /**
             * Check if the validation must be canceled if this validator fails
             */
            if (
                false === $validator->validate($this, $field) &&
                $validator->getOption("cancelOnFail")
            ) {
                break;
            }
        }

        /**
         * Get the messages generated by the validators
         */
        if (true === method_exists($this, "afterValidation")) {
            $this->afterValidation($data, $this->entity, $this->messages);
        }

        return $this->messages;
    }

    /**
     * Internal validations, if it returns true, then skip the current validator
     *
     * @param array|string       $field
     * @param ValidatorInterface $validator
     *
     * @phpstan-param mixed $field
     */
    protected function preChecking(
        mixed $field,
        ValidatorInterface $validator
    ): bool {
        $results = [];

        if (is_array($field)) {
            foreach ($field as $singleField) {
                $results[] = $this->preChecking($singleField, $validator);

                if (true === in_array(false, $results)) {
                    return false;
                }

                return true;
            }
        } else {
            /** @phpstan-var string $field */
            $allowEmpty = $validator->getOption("allowEmpty", false);

            if ($allowEmpty) {
                /**
                 * The `allowEmpty` rule is owned by the validator
                 * (AbstractValidator::isAllowEmpty() or an override)
                 */
                if (true === method_exists($validator, "isAllowEmpty")) {
                    /** @phpstan-var bool $isAllowEmpty */
                    $isAllowEmpty = $validator->isAllowEmpty($this, $field);

                    return $isAllowEmpty;
                }

                /**
                 * Compatibility path for validators implementing
                 * ValidatorInterface without extending AbstractValidator
                 */
                $value = $this->getValue($field);

                if (is_array($allowEmpty)) {
                    return in_array($value, $allowEmpty, true);
                }

                return empty($value);
            }
        }

        return false;
    }
}
