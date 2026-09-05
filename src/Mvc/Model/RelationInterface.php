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

namespace Phalcon\Mvc\Model;

use Phalcon\Contracts\Mvc\MvcTypes;

/**
 * Interface for Phalcon\Mvc\Model\Relation
 *
 * @phpstan-import-type mvc_model_parameters from MvcTypes
 * @phpstan-import-type mvc_relation_fields from MvcTypes
 * @phpstan-import-type mvc_relation_options from MvcTypes
 */
interface RelationInterface
{
    /**
     * Returns the fields
     *
     * @return array|string
     *
     * @phpstan-return mvc_relation_fields
     */
    public function getFields();

    /**
     * Returns the foreign key configuration
     *
     * @return array|bool|string
     *
     * @phpstan-return array<string, mixed>|string|bool
     */
    public function getForeignKey();

    /**
     * Gets the intermediate fields for has-*-through relations
     *
     * @return array|string
     *
     * @phpstan-return mvc_relation_fields
     */
    public function getIntermediateFields();

    /**
     * Gets the intermediate model for has-*-through relations
     *
     * @return string
     */
    public function getIntermediateModel(): string;

    /**
     * Gets the intermediate referenced fields for has-*-through relations
     *
     * @return array|string
     *
     * @phpstan-return mvc_relation_fields
     */
    public function getIntermediateReferencedFields();

    /**
     * Returns an option by the specified name
     * If the option does not exist null is returned
     *
     * @param string $name
     *
     * @return mixed
     */
    public function getOption(string $name);

    /**
     * Returns the options
     *
     * @return array
     *
     * @phpstan-return mvc_relation_options
     */
    public function getOptions(): array;

    /**
     * Returns parameters that must be always used when the related records are obtained
     *
     * @return array|false
     *
     * @phpstan-return mvc_model_parameters|false
     */
    public function getParams();

    /**
     * Returns the referenced fields
     *
     * @return array|string
     *
     * @phpstan-return mvc_relation_fields
     */
    public function getReferencedFields();

    /**
     * Returns the referenced model
     *
     * @return string
     */
    public function getReferencedModel(): string;

    /**
     * Returns the relations type
     *
     * @return int
     */
    public function getType(): int;

    /**
     * Check whether the relation act as a foreign key
     *
     * @return bool
     */
    public function isForeignKey(): bool;

    /**
     * Check if records returned by getting belongs-to/has-many are implicitly
     * cached during the current request
     *
     * @return bool
     */
    public function isReusable(): bool;

    /**
     * Check whether the relation is a 'many-to-many' relation or not
     *
     * @return bool
     */
    public function isThrough(): bool;

    /**
     * Sets the intermediate model data for has-*-through relations
     *
     * @param mixed  $intermediateFields
     * @param string $intermediateModel
     * @param mixed  $intermediateReferencedFields
     *
     * @return mixed
     *
     */
    public function setIntermediateRelation(
        mixed $intermediateFields,
        string $intermediateModel,
        mixed $intermediateReferencedFields
    );
}
