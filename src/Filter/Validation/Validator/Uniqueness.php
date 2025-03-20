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

use Phalcon\Filter\Validation;
use Phalcon\Filter\Validation\AbstractCombinedFieldsValidator;
use Phalcon\Filter\Validation\Exception;
use Phalcon\Mvc\Model;
use Phalcon\Mvc\ModelInterface;

use function array_keys;
use function get_class;
use function ini_get;
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
 */
class Uniqueness extends AbstractCombinedFieldsValidator
{
    /**
     * @var string|null
     */
    protected string | null $template = "Field :field must be unique";

    /**
     * @var array
     */
    private array $columnMap = [];

    /**
     * Executes the validation
     *
     * @param Validation $validation
     * @param string     $field
     *
     * @return bool
     * @throws Exception
     */
    public function validate(Validation $validation, string $field): bool
    {
        if (true !== $this->isUniqueness($validation, $field)) {
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
        // Caching columnMap
        $columnRenaming = (bool)ini_get("orm.column_renaming");
        if (true === $columnRenaming && empty($this->columnMap)) {
            $this->columnMap = $record
                ->getDI()
                ->getShared("modelsMetadata")
                ->getColumnMap($record)
            ;
        }

        if (isset($this->columnMap[$field])) {
            return $this->columnMap[$field];
        }

        return $field;
    }

    /**
     * @param Validation   $validation
     * @param array|string $field
     *
     * @return bool
     * @throws Exception
     */
    protected function isUniqueness(
        Validation $validation,
        array | string $field
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

        foreach ($field as $singleField) {
            $values[$singleField] = $validation->getValue($singleField);
        }

        if (null !== $convert) {
            $values = $convert($values);

            if (!is_array($values)) {
                throw new Exception("Value conversion must return an array");
            }
        }

        /** @var Model|null $record */
        $record = $this->getOption("model");

        if (empty($record) || !is_object($record)) {
            // check validation getEntity() method
            $record = $validation->getEntity();

            if (empty($record)) {
                throw new Exception(
                    "Model of record must be set to property \"model\""
                );
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
            throw new Exception(
                "The uniqueness validator works only with Phalcon\\Mvc\\Model"
            );
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
     * @param array $field
     * @param array $values
     *
     * @return array<string, list<string>|string>
     */
    protected function isUniquenessModel(
        mixed $record,
        array $field,
        array $values
    ): array {
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
                        $attribute   = $this->getColumnNameReal(
                            $record,
                            $this->getOption("attribute", $exceptKey)
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
                    $attribute = $this->getColumnNameReal(
                        $record,
                        $this->getOption("attribute", $field[0])
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
                        $attribute = $this->getColumnNameReal(
                            $record,
                            $this->getOption("attribute", $item)
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
         *
         * @todo Change this to the commented line
         */
        // if ($record->getDirtyState() == Model::DIRTY_STATE_PERSISTENT) {
        if (0 === $record->getDirtyState()) {
            $metaData   = $record->getDI()
                                 ->getShared("modelsMetadata")
            ;
            $attributes = $metaData->getPrimaryKeyAttributes($record);
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
