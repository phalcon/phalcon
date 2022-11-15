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

use Phalcon\Di\Di;
use Phalcon\Di\Injectable;
use Phalcon\Filter\Validation\AbstractCombinedFieldsValidator;
use Phalcon\Filter\Validation\Exception;
use Phalcon\Filter\Validation\ValidationInterface;
use Phalcon\Filter\Validation\ValidatorInterface;
use Phalcon\Messages\MessageInterface;
use Phalcon\Messages\Messages;
use Phalcon\Traits\Helper\Str\CamelizeTrait;

use function array_filter;
use function in_array;
use function is_array;
use function is_object;
use function is_string;
use function join;
use function method_exists;
use function property_exists;

/**
 * Allows to validate data using custom or built-in validators
 */
class Validation extends Injectable implements ValidationInterface
{
    use CamelizeTrait;

    /**
     * @var array
     */
    protected array $combinedFieldsValidators = [];

    /**
     * @var array|object
     */
    protected array|object $data;

    /**
     * @var object|null
     */
    protected ?object $entity = null;

    /**
     * @var array
     */
    protected array $filters = [];

    /**
     * @var array
     */
    protected array $labels = [];

    /**
     * @var Messages
     */
    protected Messages $messages;

    /**
     * List of validators
     *
     * @var array
     */
    protected array $validators = [];

    /**
     * Calculated values
     *
     * @var array
     */
    protected array $values = [];

    /**
     * Phalcon\Filter\Validation constructor
     *
     * @param array $validators
     */
    public function __construct(array $validators = [])
    {
        $this->messages   = new Messages();
        $this->validators = array_filter(
            $validators,
            function (array $element) {
                return true !== is_array($element[0]) ||
                    !($element[1] instanceof AbstractCombinedFieldsValidator);
            }
        );

        $this->combinedFieldsValidators = array_filter(
            $validators,
            function (array $element) {
                return true === is_array($element[0]) &&
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
     * Adds a validator to a field
     *
     * @param array|string       $field
     * @param ValidatorInterface $validator
     *
     * @return ValidationInterface
     * @throws Exception
     */
    public function add(
        array|string $field,
        ValidatorInterface $validator
    ): ValidationInterface {
        if (true === is_array($field)) {
            // Uniqueness validator for combination of fields is
            // handled differently
            if ($validator instanceof AbstractCombinedFieldsValidator) {
                $this->combinedFieldsValidators[] = [$field, $validator];
            } else {
                foreach ($field as $singleField) {
                    $this->validators[$singleField][] = $validator;
                }
            }
        } elseif (true === is_string($field)) {
            $this->validators[$field][] = $validator;
        } else {
            throw new Exception(
                "Field must be passed as array of fields or string"
            );
        }

        return $this;
    }

    /**
     * Appends a message to the messages list
     *
     * @param MessageInterface $message
     *
     * @return ValidationInterface
     */
    public function appendMessage(
        MessageInterface $message
    ): ValidationInterface {
        $this->messages->appendMessage($message);

        return $this;
    }

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
        array|object $data
    ): ValidationInterface {
        $this->setEntity($entity);

        $this->data = $data;

        return $this;
    }

    /**
     * @return array|object
     */
    public function getData(): array|object
    {
        return $this->data;
    }

    /**
     * Returns the bound entity
     *
     * @return object|null
     */
    public function getEntity(): ?object
    {
        return $this->entity;
    }

    /**
     * Returns all the filters or a specific one
     *
     * @param string|null $field
     *
     * @return mixed
     */
    public function getFilters(string $field = null): mixed
    {
        if (true === empty($field)) {
            return $this->filters;
        }

        return $this->filters[$field] ?? null;
    }

    /**
     * Get label for field
     *
     * @param array|string $field
     *
     * @return string
     */
    public function getLabel(array|string $field): string
    {
        if (true === is_array($field)) {
            return join(", ", $field);
        }

        return $this->labels[$field] ?? $field;
    }

    /**
     * Returns the registered validators
     *
     * @return Messages
     */
    public function getMessages(): Messages
    {
        return $this->messages;
    }

    /**
     * Returns the validators added to the validation
     *
     * @return array
     */
    public function getValidators(): array
    {
        return $this->validators;
    }

    /**
     * Gets the value to validate in the object entity source
     *
     * @param object $entity
     * @param string $field
     *
     * @return mixed
     */
    public function getValueByEntity(
        object $entity,
        string $field
    ): mixed {
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
     * Gets the value to validate in the array/object data source
     *
     * @param array|object $data
     * @param string       $field
     *
     * @return mixed
     */
    public function getValueByData(
        array|object $data,
        string $field
    ): mixed {
        if (true === isset($this->values[$field])) {
            return $this->values[$field];
        }

        if (true === is_array($data) && true === isset($data[$field])) {
            return $data[$field];
        }

        return $data->{$field} ?? null;
    }

    /**
     * Gets the value to validate in the array/object data source
     *
     * @param string $field
     *
     * @return mixed
     * @throws Exception
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
            if (null === $this->data) {
                throw new Exception("There is no data to validate");
            }

            $value = $this->getValueByData($this->data, $field);
        }

        if (null === $value) {
            return null;
        }

        if (true === isset($this->filters[$field])) {
            $fieldFilters = $this->filters[$field];
            if (true !== empty($fieldFilters)) {
                $container = $this->getDI();
                if (null === $this->container) {
                    $container = Di::getDefault();
                }

                $filterService = null;
                if (true === $container->has('filter')) {
                    $filterService = $container->getShared('filter');
                }

                if (!($filterService instanceof FilterInterface)) {
                    throw new Exception(
                        "Returned 'filter' service is invalid"
                    );
                }

                $filterService->sanitize($value, $fieldFilters);

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
        }

        // Cache the calculated value only if it's not entity
        if (null === $this->entity) {
            $this->values[$field] = $value;
        }

        return $value;
    }

    /**
     * Alias of `add` method
     *
     * @param array|string       $field
     * @param ValidatorInterface $validator
     *
     * @return ValidationInterface
     * @throws Exception
     * @todo remove this
     */
    public function rule(
        array|string $field,
        ValidatorInterface $validator
    ): ValidationInterface {
        return $this->add($field, $validator);
    }

    /**
     * Adds the validators to a field
     *
     * @param array|string $field
     * @param array        $validators
     *
     * @return ValidationInterface
     * @throws Exception
     */
    public function rules(
        array|string $field,
        array $validators
    ): ValidationInterface {
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
     * @param object $entity
     *
     * @return void
     */
    public function setEntity(object $entity): void
    {
        $this->entity = $entity;
    }

    /**
     * Adds filters to the field
     *
     * @param array|string $field
     * @param array|string $filters
     *
     * @return ValidationInterface
     */
    public function setFilters(
        array|string $field,
        array|string $filters
    ): ValidationInterface {
        $fields = $field;
        if (true !== is_array($field)) {
            $fields = [$field];
        }

        foreach ($fields as $singleField) {
            $this->filters[$singleField] = $filters;
        }

        return $this;
    }

    /**
     * Adds labels for fields
     *
     * @param array $labels
     *
     * @return ValidationInterface
     */
    public function setLabels(array $labels): ValidationInterface
    {
        $this->labels = $labels;

        return $this;
    }

    /**
     * Sets the validator array
     *
     * @param array $validators
     *
     * @return $this
     */
    public function setValidators(array $validators): Validation
    {
        $this->validators = $validators;

        return $this;
    }

    /**
     * Validate a set of data according to a set of rules
     *
     * @param array|object|null $data
     * @param object|null       $entity
     *
     * @return Messages|bool
     * @throws Exception
     */
    public function validate(
        array|object $data = null,
        object $entity = null
    ): Messages|false {
        /**
         * Clear pre-calculated values
         */
        $this->values = [];

        /**
         * Implicitly creates a Phalcon\Messages\Messages object
         */
        $messages = new Messages();

        if (null !== $entity) {
            $this->setEntity($entity);
        }

        /**
         * Validation classes can implement the 'beforeValidation' callback
         */
        if (true === method_exists($this, "beforeValidation")) {
            $status = $this->beforeValidation($data, $entity, $messages);

            if (false === $status) {
                return false;
            }
        }

        $this->messages = $messages;

        if (null !== $data) {
            $this->data = $data;
        }

        foreach ($this->validators as $field => $validators) {
            foreach ($validators as $validator) {
                if (true !== is_object($validator)) {
                    throw new Exception("One of the validators is not valid");
                }

                /**
                 * Call internal validations, if it returns true, then skip the
                 * current validator
                 */
                if (true === $this->preChecking($field, $validator)) {
                    continue;
                }

                /**
                 * Check if the validation must be canceled if this validator fails
                 */
                if (false === $validator->validate($this, $field)) {
                    if ($validator->getOption("cancelOnFail")) {
                        break;
                    }
                }
            }
        }

        foreach ($this->combinedFieldsValidators as $scope) {
            if (true !== is_array($scope)) {
                throw new Exception("The validator scope is not valid");
            }

            $field     = $scope[0];
            $validator = $scope[1];

            if (true !== is_object($validator)) {
                throw new Exception("One of the validators is not valid");
            }

            /**
             * Call internal validations, if it returns true, then skip the
             * current validator
             */
            if (true === $this->preChecking($field, $validator)) {
                continue;
            }

            /**
             * Check if the validation must be canceled if this validator fails
             */
            if (false === $validator->validate($this, $field)) {
                if ($validator->getOption("cancelOnFail")) {
                    break;
                }
            }
        }

        /**
         * Get the messages generated by the validators
         */
        if (true === method_exists($this, "afterValidation")) {
            $this->afterValidation($data, $entity, $this->messages);
        }

        return $this->messages;
    }

    /**
     * Internal validations, if it returns true, then skip the current validator
     *
     * @param array|string       $field
     * @param ValidatorInterface $validator
     *
     * @return bool
     * @throws Exception
     */
    protected function preChecking(
        array|string $field,
        ValidatorInterface $validator
    ): bool {
        $results = [];

        if (true === is_array($field)) {
            foreach ($field as $singleField) {
                $results[] = $this->preChecking($singleField, $validator);

                if (true === in_array(false, $results)) {
                    return false;
                }

                return true;
            }
        } else {
            $allowEmpty = $validator->getOption("allowEmpty", false);

            if ($allowEmpty) {
                if (true === method_exists($validator, "isAllowEmpty")) {
                    return $validator->isAllowEmpty($this, $field);
                }

                $value = $this->getValue($field);

                if (true === is_array($allowEmpty)) {
                    if (in_array($value, $allowEmpty, true)) {
                        return true;
                    }

                    return false;
                }

                return empty($value);
            }
        }

        return false;
    }
}
