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

namespace Phalcon\Filter\Validation\Validator;

use Phalcon\Contracts\Filter\FilterTypes;
use Phalcon\Di\DiInterface;
use Phalcon\Di\InjectionAwareInterface;
use Phalcon\Filter\Validation;
use Phalcon\Filter\Validation\AbstractCombinedFieldsValidator;
use Phalcon\Filter\Validation\Exception;
use Phalcon\Filter\Validation\Exceptions\UniquenessConversionMustBeArray;
use Phalcon\Filter\Validation\Exceptions\UniquenessModelRequired;
use Phalcon\Filter\Validation\Exceptions\UniquenessOnlyForPhalconModel;
use Phalcon\Messages\Message;
use Phalcon\Mvc\EntityInterface;
use Phalcon\Mvc\Model;
use Phalcon\Mvc\Model\MetaDataInterface;
use Phalcon\Mvc\ModelInterface;
use Phalcon\Support\Settings;

use function array_keys;
use function get_class;
use function is_array;
use function is_object;
use function range;

/**
 * Check that a field is unique in the related table
 *
 * ```php
 * use Phalcon\Filter\Validation;
 * use Phalcon\Filter\Validation\Validator\Uniqueness as UniquenessValidator;
 *
 * $validator = new Validation();
 *
 * $validator->add(
 *     "username",
 *     new UniquenessValidator(
 *         [
 *             "model"   => new Users(),
 *             "message" => ":field must be unique",
 *         ]
 *     )
 * );
 * ```
 *
 * Different attribute from the field:
 * ```php
 * $validator->add(
 *     "username",
 *     new UniquenessValidator(
 *         [
 *             "model"     => new Users(),
 *             "attribute" => "nick",
 *         ]
 *     )
 * );
 * ```
 *
 * In model:
 * ```php
 * $validator->add(
 *     "username",
 *     new UniquenessValidator()
 * );
 * ```
 *
 * Combination of fields in model:
 * ```php
 * $validator->add(
 *     [
 *         "firstName",
 *         "lastName",
 *     ],
 *     new UniquenessValidator()
 * );
 * ```
 *
 * It is possible to convert values before validation. This is useful in
 * situations where values need to be converted to do the database lookup:
 *
 * ```php
 * $validator->add(
 *     "username",
 *     new UniquenessValidator(
 *         [
 *             "convert" => function (array $values) {
 *                 $values["username"] = strtolower($values["username"]);
 *
 *                 return $values;
 *             }
 *         ]
 *     )
 * );
 * ```
 *
 * @phpstan-import-type filter_uniqueness_column_map from FilterTypes
 * @phpstan-import-type filter_uniqueness_fields from FilterTypes
 * @phpstan-import-type filter_uniqueness_params from FilterTypes
 * @phpstan-import-type filter_uniqueness_values from FilterTypes
 * @phpstan-import-type filter_validator_options from FilterTypes
 */
class Uniqueness extends AbstractCombinedFieldsValidator
{
    /**
     * @var string|null
     */
    protected string | null $template = "Field :field must be unique";

    /**
     * @phpstan-var filter_uniqueness_column_map|null
     */
    private array | null $columnMap = null;

    /**
     * Constructor
     *
     * @phpstan-param filter_validator_options $options
     */
    public function __construct(array $options = [])
    {
        parent::__construct($options);
    }

    /**
     * Returns an option in the validator's options
     * Returns null if the option hasn't set
     *
     * The `attribute` option can be defined as an array when validating a
     * combination of fields; in that case resolve it to the mapped value.
     *
     * @param string     $key
     * @param mixed|null $defaultValue
     *
     * @return mixed
     */
    public function getOption(string $key, mixed $defaultValue = null): mixed
    {
        if (!$this->hasOption($key)) {
            return $defaultValue;
        }

        $value = parent::getOption($key, $defaultValue);

        if (
            "attribute" === $key &&
            is_array($value) &&
            isset($value[$key])
        ) {
            return $value[$key];
        }

        return $value;
    }

    /**
     * Executes the validation
     *
     * @param Validation $validation
     * @param mixed      $field
     *
     * @return bool
     */
    public function validate(Validation $validation, mixed $field): bool
    {
        if (!$this->isUniqueness($validation, $field)) {
            $validation->appendMessage(
                $this->messageFactory($validation, $field)
            );

            return false;
        }

        return true;
    }

    /**
     * The column map is used in the case to get real column name
     *
     * @param mixed  $record
     * @param string $field
     *
     * @return string
     */
    protected function getColumnNameReal(mixed $record, string $field): string
    {
        /**
         * `isUniqueness()` only reaches the model path for a model record.
         *
         * @var EntityInterface&InjectionAwareInterface&ModelInterface<mixed> $record
         */
        // Caching columnMap
        if (Settings::get("orm.column_renaming") && !$this->columnMap) {
            /** @var DiInterface $container */
            $container = $record->getDI();
            /** @var MetaDataInterface $metaData */
            $metaData = $container->getShared("modelsMetadata");
            /** @var filter_uniqueness_column_map|null $columnMap */
            $columnMap = $metaData->getColumnMap($record);

            $this->columnMap = $columnMap;
        }

        if (is_array($this->columnMap) && isset($this->columnMap[$field])) {
            return $this->columnMap[$field];
        }

        return $field;
    }

    /**
     * @param Validation   $validation
     * @param mixed        $field
     *
     * @return bool
     * @throws Exception
     */
    protected function isUniqueness(
        Validation $validation,
        mixed $field
    ): bool {
//
// @todo: Restore when new Collection is reintroduced
//
//        var isDocument;

        if (!is_array($field)) {
            $field = [$field];
        }

        $values  = [];
        $convert = $this->getOption("convert");

        /**
         * A field list holds field names.
         *
         * @var array<array-key, string> $field
         */
        foreach ($field as $singleField) {
            $values[$singleField] = $validation->getValue($singleField);
        }

        if (null !== $convert) {
            /**
             * The `convert` option is a callable that reshapes the values.
             *
             * @var callable(array<array-key, mixed>): mixed $convert
             */
            $values = $convert($values);

            if (!is_array($values)) {
                throw new UniquenessConversionMustBeArray();
            }
        }

        /**
         * The values are keyed by field name.
         *
         * @var filter_uniqueness_values $values
         */

        $record = $this->getOption("model");

        if (empty($record) || !is_object($record)) {
            // check validation getEntity() method
            $record = $validation->getEntity();

            if (empty($record)) {
                throw new UniquenessModelRequired();
            }
        }

        $isModel = $record instanceof ModelInterface;
//
// @todo: Restore when new Collection is reintroduced
//
//        let isDocument = record instanceof CollectionInterface;

        if (true === $isModel) {
            $params = $this->isUniquenessModel($record, $field, $values);
//
// @todo: Restore when new Collection is reintroduced
//
//        } elseif isDocument {
//            let params = this->isUniquenessCollection(record, field, values);
        } else {
            throw new UniquenessOnlyForPhalconModel();
//
// @todo: Restore when new Collection is reintroduced
//
//            throw new Exception(
//                "The uniqueness validator works only with Phalcon\\Mvc\\Model or Phalcon\\Mvc\\Collection"
//            );
        }

        /** @var class-string $className */
        $className = get_class($record);

        return $className::count($params) === 0;
    }


//
// @todo: Restore when new Collection is reintroduced
//
//    /**
//     * Uniqueness method used for collection
//     */
//    protected function isUniquenessCollection(var record, array field, array values)
//    {
//        var exceptConditions, fieldExcept, notInValues, value, singleField,
//            params, except, singleExcept;
//
//        let exceptConditions = [];
//        let params = [
//            "conditions" : []
//        ];
//
//        for singleField in field {
//            let fieldExcept = null;
//            let notInValues = [];
//            let value = values[singleField];
//
//            let except = this->getOption("except");
//
//            let params["conditions"][singleField] = value;
//
//            if except {
//                if typeof except == "array" && count(field) > 1 {
//                    if isset except[singleField] {
//                        let fieldExcept = except[singleField];
//                    }
//                }
//
//                if fieldExcept != null {
//                    if typeof fieldExcept == "array" {
//                        for singleExcept in fieldExcept {
//                            let notInValues[] = singleExcept;
//                        }
//
//                        let exceptConditions[singleField] = [
//                            "$nin": notInValues
//                        ];
//                    } else {
//                        let exceptConditions[singleField] = [
//                            "$ne": fieldExcept
//                        ];
//                    }
//                } elseif typeof except == "array" && count(field) == 1 {
//                    for singleExcept in except {
//                        let notInValues[] = singleExcept;
//                    }
//
//                    let params["conditions"][singleField] = [
//                        "$nin": notInValues
//                    ];
//                } elseif count(field) == 1 {
//                    let params["conditions"][singleField] = [
//                        "$ne": except
//                    ];
//                }
//            }
//        }
//
//        if record->getDirtyState() == Collection::DIRTY_STATE_PERSISTENT {
//            let params["conditions"]["_id"] = [
//                "$ne": record->getId()
//            ];
//        }
//
//        if !empty exceptConditions {
//            let params["conditions"]["$or"] = [exceptConditions];
//        }
//
//        return params;
//    }

    /**
     * Uniqueness method used for model
     *
     * @param mixed $record
     *
     * @phpstan-param filter_uniqueness_fields $field
     * @phpstan-param filter_uniqueness_values $values
     *
     * @phpstan-return filter_uniqueness_params
     */
    protected function isUniquenessModel(
        mixed $record,
        array $field,
        array $values
    ) {
        /**
         * `isUniqueness()` only reaches this method for a model record.
         *
         * @var EntityInterface&InjectionAwareInterface&ModelInterface<mixed> $record
         */
        $exceptConditions = [];
        $index            = 0;
        $params           = [
            "conditions" => [],
            "bind"       => [],
        ];
        $except           = $this->getOption("except");

        foreach ($field as $singleField) {
            $fieldExcept = null;
            $notInValues = [];
            $value       = $values[$singleField];

            /** @var string $attribute */
            $attribute = $this->getOption("attribute", $singleField);
            $attribute = $this->getColumnNameReal($record, $attribute);

            if (null !== $value) {
                $params["conditions"][] = $attribute . " = ?" . $index;
                $params["bind"][]       = $value;
                $index++;
            } else {
                $params["conditions"][] = $attribute . " IS NULL";
            }

            if ($except) {
                if (
                    is_array($except) &&
                    array_keys($except) !== range(0, count($except) - 1)
                ) {
                    foreach ($except as $exceptKey => $fieldExcept) {
                        $notInValues = [];
                        /** @var string $exceptAttribute */
                        $exceptAttribute = $this->getOption("attribute", $exceptKey);
                        $attribute       = $this->getColumnNameReal(
                            $record,
                            $exceptAttribute
                        );

                        if (is_array($fieldExcept)) {
                            foreach ($fieldExcept as $singleExcept) {
                                $notInValues[]    = "?" . $index;
                                $params["bind"][] = $singleExcept;
                                $index++;
                            }

                            $exceptConditions[] = $attribute
                                . " NOT IN ("
                                . implode(",", $notInValues)
                                . ")";
                        } else {
                            $exceptConditions[] = $attribute . " <> ?" . $index;
                            $params["bind"][]   = $fieldExcept;
                            $index++;
                        }
                    }
                } elseif (count($field) === 1) {
                    /** @var string $firstAttribute */
                    $firstAttribute = $this->getOption("attribute", $field[0]);
                    $attribute      = $this->getColumnNameReal(
                        $record,
                        $firstAttribute
                    );

                    if (is_array($except)) {
                        foreach ($except as $singleExcept) {
                            $notInValues[]    = "?" . $index;
                            $params["bind"][] = $singleExcept;
                            $index++;
                        }

                        $exceptConditions[] = $attribute
                            . " NOT IN ("
                            . implode(",", $notInValues)
                            . ")";
                    } else {
                        $params["conditions"][] = $attribute . " <> ?" . $index;
                        $params["bind"][]       = $except;
                        $index++;
                    }
                } elseif (count($field) > 1) {
                    foreach ($field as $item) {
                        /** @var string $itemAttribute */
                        $itemAttribute = $this->getOption("attribute", $item);
                        $attribute     = $this->getColumnNameReal(
                            $record,
                            $itemAttribute
                        );

                        if (is_array($except)) {
                            foreach ($except as $singleExcept) {
                                $notInValues[]    = "?" . $index;
                                $params["bind"][] = $singleExcept;
                                $index++;
                            }

                            $exceptConditions[] = $attribute
                                . " NOT IN ("
                                . implode(",", $notInValues)
                                . ")";
                        } else {
                            $params["conditions"][] = $attribute . " <> ?" . $index;
                            $params["bind"][]       = $except;
                            $index++;
                        }
                    }
                }
            }
        }

        /**
         * If the operation is update, there must be values in the object
         */
        if ($record->getDirtyState() == Model::DIRTY_STATE_PERSISTENT) {
            /** @var DiInterface $container */
            $container = $record->getDI();
            /** @var MetaDataInterface $metaData */
            $metaData = $container->getShared("modelsMetadata");

            $attributes = $metaData->getPrimaryKeyAttributes($record);

            /**
             * The metadata service returns the primary key column names.
             *
             * @var array<array-key, string> $attributes
             */
            foreach ($attributes as $primaryField) {
                $params["conditions"][] = $this->getColumnNameReal(
                    $record,
                    $primaryField
                ) . " <> ?" . $index;

                $params["bind"][] = $record->readAttribute(
                    $this->getColumnNameReal($record, $primaryField)
                );

                $index++;
            }
        }

        if (!empty($exceptConditions)) {
            $params["conditions"][] = "("
                . implode(" OR ", $exceptConditions)
                . ")";
        }

        $params["conditions"] = implode(
            " AND ",
            $params["conditions"]
        );

        return $params;
    }
}
