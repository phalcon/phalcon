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

/**
 * Interface for Phalcon\Mvc\Model\Relation
 */
interface RelationInterface
{
    /**
     * Returns the fields
     *
     * @return array|string
     */
    public function getFields(): array | string;

    /**
     * Returns the foreign key configuration
     *
     * @return array|false|string
     */
    public function getForeignKey(): array | false | string;

    /**
     * Gets the intermediate fields for has-*-through relations
     *
     * @return array|string
     */
    public function getIntermediateFields(): array | string;

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
     */
    public function getIntermediateReferencedFields(): array | string;

    /**
     * Returns an option by the specified name
     * If the option doesn't exist null is returned
     *
     * @param string $name
     *
     * @return mixed
     */
    public function getOption(string $name): mixed;

    /**
     * Returns the options
     *
     * @return array
     */
    public function getOptions(): array;

    /**
     * Returns parameters that must be always used when the related records are obtained
     *
     * @return array|false
     */
    public function getParams(): array | false;

    /**
     * Returns the referenced fields
     *
     * @return array|string
     */
    public function getReferencedFields(): array | string;

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
     * @param array|string $intermediateFields
     * @param string       $intermediateModel
     * @param array|string $intermediateReferencedFields
     *
     * @return mixed
     */
    public function setIntermediateRelation(
        array | string $intermediateFields,
        string $intermediateModel,
        array | string $intermediateReferencedFields
    );
}
