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

namespace Phalcon\Mvc\Model\Eager;

use Phalcon\Contracts\Mvc\MvcTypes;
use Phalcon\Mvc\EntityInterface;
use Phalcon\Mvc\Model\Exceptions\EagerRowLimitExceeded;
use Phalcon\Mvc\Model\Exceptions\MissingEagerKeyColumn;
use Phalcon\Mvc\Model\Exceptions\UnknownEagerRelation;
use Phalcon\Mvc\Model\Manager;
use Phalcon\Mvc\Model\ManagerInterface;
use Phalcon\Mvc\Model\Relation;
use Phalcon\Mvc\Model\RelationInterface;
use Phalcon\Mvc\Model\Resultset\Simple;
use Phalcon\Mvc\ModelInterface;

use function array_column;
use function array_key_exists;
use function array_merge;
use function array_values;
use function count;
use function implode;
use function in_array;
use function is_array;
use function is_object;
use function str_replace;
use function strlen;
use function strpos;
use function strtolower;

/**
 * Loads model relations in bulk - a bounded number of queries per relation
 * node rather than one per record - and applies the result to records as they
 * are hydrated.
 *
 * @phpstan-import-type mvc_eager_map from MvcTypes
 * @phpstan-import-type mvc_eager_map_node from MvcTypes
 * @phpstan-import-type mvc_eager_node from MvcTypes
 * @phpstan-import-type mvc_eager_parents from MvcTypes
 * @phpstan-import-type mvc_model_parameters from MvcTypes
 * @phpstan-import-type mvc_query_columns from MvcTypes
 * @phpstan-import-type mvc_relation_fields from MvcTypes
 */
class Loader
{
    /**
     * Maximum number of rows a single relation node may return before the load
     * is refused. Guards against a to-many hop that follows a to-one hop, which
     * can fan out to an entire table.
     *
     * @var int
     */
    public const MAX_ROWS_PER_LEVEL = 100000;

    /**
     * @param ManagerInterface $manager
     */
    public function __construct(
        protected ManagerInterface $manager
    ) {
    }

    /**
     * Applies a pre-built eager map to a single record.
     *
     * Shared by Resultset\Simple::current(), which stamps records as they are
     * hydrated, and by the loader itself, which stamps instances it retains.
     *
     * Both Model and Row implement readAttribute(), so key extraction is
     * uniform; only the write differs. A Row is what a column-restricted
     * select produces, and it has no relation cache.
     *
     * @param mixed $record   ModelInterface or Row
     * @param array $eagerMap
     *
     * @return void
     *
     * @phpstan-param mvc_eager_map                                $eagerMap
     */
    public static function apply(mixed $record, array $eagerMap): void
    {
        foreach ($eagerMap as $alias => $node) {
            // Model and Row both implement EntityInterface.
            /** @var EntityInterface $record */
            $values = [];

            foreach ($node["fields"] as $field) {
                $values[] = $record->readAttribute($field);
            }

            $records = $node["records"];
            $related = $node["empty"];

            if (!in_array(null, $values, true)) {
                $lookup = self::buildKey($values);

                if (isset($records[$lookup])) {
                    $related = $records[$lookup];
                }
            }

            if ($record instanceof ModelInterface) {
                // setRelated() is declared by the model class.
                /** @var \Phalcon\Mvc\Model $record */
                $record->setRelated($alias, $related);
            } else {
                $record->writeAttribute($alias, $related);
            }
        }
    }

    /**
     * Builds the lookup key for a set of key-field values.
     *
     * Always a string. A single value is cast, which also neutralizes the
     * PostgreSQL-integer / MySQL-string mismatch for the same column. Multiple
     * values are length-prefixed so ["a|b", "c"] cannot collide with
     * ["a", "b|c"].
     *
     * @param array $values
     *
     * @return string
     *
     * @phpstan-param array<array-key, mixed> $values
     */
    public static function buildKey(array $values): string
    {
        // The key fields of a record hold scalar values.
        /** @var array<array-key, scalar|null> $values */
        if (count($values) === 1) {
            return (string)$values[0];
        }

        $key = "";

        foreach ($values as $value) {
            $part = (string)$value;
            $key .= strlen($part) . ":" . $part;
        }

        return $key;
    }

    /**
     * Loads a relation tree for a root resultset.
     *
     * The resultset is materialized first: at this point the statement has run
     * but no row has been consumed, so fetching every row costs nothing extra
     * and gives the key values without a second pass over the cursor.
     *
     * @param Simple $resultset
     * @param string $modelName
     * @param array  $tree
     *
     * @return void
     *
     * @phpstan-param array<string, mixed> $tree
     */
    public function loadResultset(
        Simple $resultset,
        string $modelName,
        array $tree
    ): void {
        if (empty($tree) || $resultset->count() === 0) {
            return;
        }

        $resultset->materialize();

        $resultset->setEagerMap(
            $this->buildMap($resultset->toArray(), $modelName, $tree)
        );
    }

    /**
     * Builds one level of the map.
     *
     * @param array  $parents   attribute-keyed row arrays at the root, or
     *                          ModelInterface / Row instances below it
     * @param string $modelName
     * @param array  $tree
     *
     * @return array
     *
     * @phpstan-param mvc_eager_parents    $parents
     * @phpstan-param array<string, mixed> $tree
     * @phpstan-return mvc_eager_map
     */
    protected function buildMap(
        array $parents,
        string $modelName,
        array $tree
    ): array {
        $map = [];

        foreach ($tree as $alias => $node) {
            /** @var mvc_eager_node $node */
            $relation = $this->manager->getRelationByAlias($modelName, $alias);

            if (!is_object($relation)) {
                throw new UnknownEagerRelation($modelName, $alias);
            }

            $map[strtolower($alias)] = $this->buildNode(
                $relation,
                $alias,
                $parents,
                $node
            );
        }

        return $map;
    }

    /**
     * Builds a single map node: one query, indexed by the referenced field.
     *
     * @param RelationInterface $relation
     * @param string            $alias
     * @param array             $parents
     * @param array             $node
     *
     * @return array
     *
     * @phpstan-param mvc_eager_parents $parents
     * @phpstan-param mvc_eager_node    $node
     * @phpstan-return mvc_eager_map_node
     */
    protected function buildNode(
        RelationInterface $relation,
        string $alias,
        array $parents,
        array $node
    ): array {
        if ($relation->isThrough()) {
            return $this->buildThroughNode($relation, $alias, $parents, $node);
        }

        $fields           = $this->normalizeFields($relation->getFields());
        $referencedFields = $this->normalizeFields(
            $relation->getReferencedFields()
        );

        $keys = $this->collectKeys($parents, $fields, $alias);

        $children = $this->fetchReferenced(
            $relation,
            $alias,
            $keys,
            $node["options"]
        );

        $childModels = [];
        $index       = [];
        $position    = 0;

        $children->rewind();

        while ($children->valid()) {
            /** @var \Phalcon\Mvc\Model|\Phalcon\Mvc\Model\Row $record */
            $record = $children->current();

            $childModels[] = $record;

            $index[$this->recordKey($record, $referencedFields)][] = $position;

            $position++;

            $children->next();
        }

        $isMany      = $relation->getType() === Relation::HAS_MANY;
        $emptyResult = $isMany ? $children->sliceRows([]) : null;

        $records = [];

        foreach ($index as $keyValue => $positions) {
            if ($isMany) {
                $records[$keyValue] = $children->sliceRows($positions);
            } else {
                $records[$keyValue] = $childModels[$positions[0]];
            }
        }

        /**
         * Levels below the root are cheaper than the root: the loader is
         * holding real instances it built itself, so there is no transient
         * hydration to work around.
         */
        if (!empty($node["children"]) && !empty($childModels)) {
            $childMap = $this->buildMap(
                $childModels,
                $relation->getReferencedModel(),
                $node["children"]
            );

            if ($isMany) {
                /**
                 * The caller never sees `childModels` for a to-many relation -
                 * the slices hydrate their own instances - so the map has to
                 * travel with the slices.
                 */
                /** @var Simple $slice */
                foreach ($records as $slice) {
                    $slice->setEagerMap($childMap);
                }

                /** @var Simple $emptyResult */
                $emptyResult->setEagerMap($childMap);
            } else {
                foreach ($childModels as $record) {
                    self::apply($record, $childMap);
                }
            }
        }

        return [
            "fields"  => $fields,
            "records" => $records,
            "empty"   => $emptyResult,
        ];
    }

    /**
     * Through-relations in two steps rather than a join.
     *
     * Step one fetches (parentKey, referencedKey) pairs from the intermediate
     * model; step two fetches the referenced rows for the keys those pairs
     * collected. The pairs then attribute referenced rows back to parents
     * without a synthetic column in the select list, and without the row
     * multiplication an inner join would cause.
     *
     * @param RelationInterface $relation
     * @param string            $alias
     * @param array             $parents
     * @param array             $node
     *
     * @return array
     *
     * @phpstan-param mvc_eager_parents $parents
     * @phpstan-param mvc_eager_node    $node
     * @phpstan-return mvc_eager_map_node
     */
    protected function buildThroughNode(
        RelationInterface $relation,
        string $alias,
        array $parents,
        array $node
    ): array {
        $fields             = $this->normalizeFields($relation->getFields());
        $intermediateFields = $this->normalizeFields(
            $relation->getIntermediateFields()
        );
        $referencedFields   = $this->normalizeFields(
            $relation->getReferencedFields()
        );

        $intermediateReferencedFields = $this->normalizeFields(
            $relation->getIntermediateReferencedFields()
        );

        $intermediateField           = $intermediateFields[0];
        $intermediateReferencedField = $intermediateReferencedFields[0];
        $intermediateModel           = $relation->getIntermediateModel();

        $isMany = $relation->getType() === Relation::HAS_MANY_THROUGH;

        $keys = $this->collectKeys($parents, $fields, $alias);

        $pairMap        = [];
        $referencedKeys = [];

        if (!empty($keys)) {
            /**
             * Step one - the intermediate pairs. Only the two key columns are
             * selected, so these come back as Row objects.
             */
            $intermediate = $this->manager->load($intermediateModel);

            /** @var Simple $pairs */
            $pairs = $intermediate::find(
                [
                    "[" . $intermediateField . "] IN ({phEagerKeys:array})",
                    "columns" => $intermediateField . ", "
                        . $intermediateReferencedField,
                    "bind"    => ["phEagerKeys" => array_column($keys, 0)],
                ]
            );

            $pairs->rewind();

            while ($pairs->valid()) {
                // A column-restricted select returns Row instances.
                /** @var \Phalcon\Mvc\Model\Row $pair */
                $pair = $pairs->current();

                $parentKey = self::buildKey(
                    [$pair->readAttribute($intermediateField)]
                );

                $referencedKey = self::buildKey(
                    [$pair->readAttribute($intermediateReferencedField)]
                );

                $pairMap[$parentKey][]          = $referencedKey;
                $referencedKeys[$referencedKey] = [
                    $pair->readAttribute($intermediateReferencedField),
                ];

                $pairs->next();
            }
        }

        /**
         * Step two - the referenced rows for the collected keys.
         */
        $referenced = $this->fetchReferenced(
            $relation,
            $alias,
            array_values($referencedKeys),
            $node["options"]
        );

        $childModels = [];
        $index       = [];
        $position    = 0;

        $referenced->rewind();

        while ($referenced->valid()) {
            /** @var \Phalcon\Mvc\Model|\Phalcon\Mvc\Model\Row $record */
            $record = $referenced->current();

            $childModels[] = $record;

            $index[$this->recordKey($record, $referencedFields)][] = $position;

            $position++;

            $referenced->next();
        }

        $emptyResult = $isMany ? $referenced->sliceRows([]) : null;

        $records = [];

        foreach ($pairMap as $parentKey => $referencedKeyList) {
            $tuple = [];

            foreach ($referencedKeyList as $referencedKey) {
                if (isset($index[$referencedKey])) {
                    $tuple = array_merge($tuple, $index[$referencedKey]);
                }
            }

            if (empty($tuple)) {
                continue;
            }

            if ($isMany) {
                $records[$parentKey] = $referenced->sliceRows($tuple);
            } else {
                $records[$parentKey] = $childModels[$tuple[0]];
            }
        }

        if (!empty($node["children"]) && !empty($childModels)) {
            $childMap = $this->buildMap(
                $childModels,
                $relation->getReferencedModel(),
                $node["children"]
            );

            if ($isMany) {
                /** @var Simple $slice */
                foreach ($records as $slice) {
                    $slice->setEagerMap($childMap);
                }

                /** @var Simple $emptyResult */
                $emptyResult->setEagerMap($childMap);
            } else {
                foreach ($childModels as $record) {
                    self::apply($record, $childMap);
                }
            }
        }

        return [
            "fields"  => $fields,
            "records" => $records,
            "empty"   => $emptyResult,
        ];
    }

    /**
     * Distinct, non-null local key tuples across the parent set.
     *
     * @param array  $parents attribute-keyed row arrays, ModelInterface or Row
     * @param array  $fields
     * @param string $alias
     *
     * @return array list of value-tuples, deduped
     *
     * @phpstan-param mvc_eager_parents         $parents
     * @phpstan-param array<array-key, string>  $fields
     * @phpstan-return list<array<array-key, mixed>>
     */
    protected function collectKeys(
        array $parents,
        array $fields,
        string $alias
    ): array {
        $seen = [];

        foreach ($parents as $parent) {
            $values = [];

            foreach ($fields as $field) {
                if (is_object($parent)) {
                    $value = $parent->readAttribute($field);
                } else {
                    if (!array_key_exists($field, $parent)) {
                        throw new MissingEagerKeyColumn($alias, $field);
                    }

                    $value = $parent[$field];
                }

                $values[] = $value;
            }

            if (in_array(null, $values, true)) {
                continue;
            }

            $seen[self::buildKey($values)] = $values;
        }

        return array_values($seen);
    }

    /**
     * One query per relation node. An empty key set issues none at all -
     * WHERE IN () is a syntax error and there is nothing to attribute.
     *
     * @param RelationInterface $relation
     * @param string            $alias
     * @param array             $keys
     * @param array             $options
     *
     * @return Simple
     *
     * @phpstan-param array<array-key, array<array-key, mixed>> $keys
     * @phpstan-param mvc_model_parameters                      $options
     */
    protected function fetchReferenced(
        RelationInterface $relation,
        string $alias,
        array $keys,
        array $options
    ): Simple {
        $referencedModel = $relation->getReferencedModel();
        $modelInstance   = $this->manager->load($referencedModel);

        if (empty($keys)) {
            return new Simple(null, $modelInstance, false);
        }

        $referencedFields = $this->normalizeFields(
            $relation->getReferencedFields()
        );

        if (count($referencedFields) === 1) {
            $findParams = [
                "[" . $referencedFields[0] . "] IN ({phEagerKeys:array})",
                "bind" => ["phEagerKeys" => array_column($keys, 0)],
            ];
        } else {
            /**
             * Row-value predicates - (a, b) IN ((1, 2)) - are not supported
             * uniformly across MySQL, PostgreSQL and SQLite, so composite keys
             * are expressed as OR-grouped equality instead.
             */
            $binds    = [];
            $groups   = [];
            $keyIndex = 0;

            foreach ($keys as $tuple) {
                $parts = [];

                foreach ($referencedFields as $fieldIndex => $referencedField) {
                    $placeholder = "phEagerKey" . $keyIndex . "_" . $fieldIndex;

                    $parts[] = "[" . $referencedField . "] = :"
                        . $placeholder . ":";

                    $binds[$placeholder] = $tuple[$fieldIndex];
                }

                $groups[] = "(" . implode(" AND ", $parts) . ")";

                $keyIndex++;
            }

            $findParams = [
                implode(" OR ", $groups),
                "bind" => $binds,
            ];
        }

        /**
         * A relation may carry conditions of its own. Ignoring them returns
         * unfiltered children - wrong data that no query-count assertion would
         * catch. getParams() returns false when unset and invokes a closure
         * before returning; the closure takes no arguments, so evaluating it
         * once per batch is equivalent.
         *
         * The eager predicate is passed as the second argument in both merges
         * so that its bindings survive - mergeFindParameters() only merges the
         * second argument's `bind` entry.
         */
        $extraParameters = $relation->getParams();

        if (is_array($extraParameters)) {
            $findParams = Manager::mergeFindParameters(
                $extraParameters,
                $findParams
            );
        }

        if (!empty($options)) {
            $findParams = Manager::mergeFindParameters($options, $findParams);
        }

        /**
         * A restricted column list that omits the join key leaves every
         * returned row unattributable. Refuse rather than inject a column the
         * caller did not ask for - it would surface in the returned Row.
         */
        if (isset($findParams["columns"])) {
            /** @var mvc_query_columns $columns */
            $columns = $findParams["columns"];

            $columnList = is_array($columns)
                ? implode(",", $columns)
                : (string)$columns;

            $columnList = str_replace(" ", "", $columnList);

            foreach ($referencedFields as $referencedField) {
                if (strpos($columnList, $referencedField) === false) {
                    throw new MissingEagerKeyColumn($alias, $referencedField);
                }
            }
        }

        /** @var Simple $resultset */
        $resultset = $modelInstance::find($findParams);

        $resultset->materialize();

        if ($resultset->count() > self::MAX_ROWS_PER_LEVEL) {
            throw new EagerRowLimitExceeded(
                $referencedModel,
                $resultset->count(),
                self::MAX_ROWS_PER_LEVEL
            );
        }

        return $resultset;
    }

    /**
     * Relation fields are declared as a string for a single column and an
     * array for a composite key. Normalizing removes that fork everywhere
     * downstream.
     *
     * @param mixed $fields
     *
     * @return array
     *
     * @phpstan-param mvc_relation_fields $fields
     * @phpstan-return array<array-key, string>
     */
    protected function normalizeFields(mixed $fields): array
    {
        if (is_array($fields)) {
            return $fields;
        }

        return [$fields];
    }

    /**
     * Lookup key for an already-hydrated record.
     *
     * @param mixed $record
     * @param array $fields
     *
     * @return string
     *
     * @phpstan-param array<array-key, string> $fields
     */
    protected function recordKey(mixed $record, array $fields): string
    {
        // The caller passes a hydrated record. Model and Row both implement
        // EntityInterface.
        /** @var EntityInterface $record */
        $values = [];

        foreach ($fields as $field) {
            $values[] = $record->readAttribute($field);
        }

        return self::buildKey($values);
    }
}
