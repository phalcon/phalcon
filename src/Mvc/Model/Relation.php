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

use function call_user_func;
use function is_callable;

/**
 * This class represents a relationship between two models
 */
class Relation implements RelationInterface
{
    public const ACTION_CASCADE   = 2;
    public const ACTION_RESTRICT  = 1;
    public const BELONGS_TO       = 0;
    public const HAS_MANY         = 2;
    public const HAS_MANY_THROUGH = 4;
    public const HAS_ONE          = 1;
    public const HAS_ONE_THROUGH  = 3;
    public const NO_ACTION        = 0;

    /**
     * @var array|string
     */
    protected array | string $intermediateFields;

    /**
     * @var string|null
     */
    protected string | null $intermediateModel = null;

    /**
     * @var array|string
     */
    protected array | string $intermediateReferencedFields;

    /**
     * Phalcon\Mvc\Model\Relation constructor
     *
     * @param int          $type
     * @param string       $referencedModel
     * @param array|string $fields
     * @param array|string $referencedFields
     * @param array        $options
     */
    public function __construct(
        protected int $type,
        protected string $referencedModel,
        protected array | string $fields,
        protected array | string $referencedFields,
        protected array $options = []
    ) {
    }

    /**
     * Returns the fields
     *
     * @return array|string
     */
    public function getFields(): array | string
    {
        return $this->fields;
    }

    /**
     * Returns the foreign key configuration
     *
     * @return array|false|string
     */
    public function getForeignKey(): array | false | string
    {
        if (isset($this->options["foreignKey"]) && !empty($this->options["foreignKey"])) {
            return $this->options["foreignKey"];
        }

        return false;
    }

    /**
     * Gets the intermediate fields for has-*-through relations
     *
     * @return array|string
     */
    public function getIntermediateFields(): array | string
    {
        return $this->intermediateFields;
    }

    /**
     * Gets the intermediate model for has-*-through relations
     */
    public function getIntermediateModel(): string
    {
        return $this->intermediateModel;
    }

    /**
     * Gets the intermediate referenced fields for has-*-through relations
     *
     * @return array|string
     */
    public function getIntermediateReferencedFields(): array | string
    {
        return $this->intermediateReferencedFields;
    }

    /**
     * Returns an option by the specified name
     * If the option doesn't exist null is returned
     *
     * @param string $name
     *
     * @return mixed
     */
    public function getOption(string $name): mixed
    {
        return $this->options[$name] ?? null;
    }

    /**
     * Returns the options
     *
     * @return array
     */
    public function getOptions(): array
    {
        return $this->options;
    }

    /**
     * Returns parameters that must be always used when the related records are obtained
     *
     * @return array|false
     */
    public function getParams(): array | false
    {
        if (
            isset($this->options["params"]) &&
            !empty($this->options["params"])
        ) {
            if (is_callable($this->options["params"])) {
                return call_user_func($this->options["params"]);
            }

            return $this->options["params"];
        }

        return false;
    }

    /**
     * Returns the referenced fields
     *
     * @return array|string
     */
    public function getReferencedFields(): array | string
    {
        return $this->referencedFields;
    }

    /**
     * Returns the referenced model
     *
     * @return string
     */
    public function getReferencedModel(): string
    {
        return $this->referencedModel;
    }

    /**
     * Returns the relation type
     *
     * @return int
     */
    public function getType(): int
    {
        return $this->type;
    }

    /**
     * Check whether the relation act as a foreign key
     *
     * @return bool
     */
    public function isForeignKey(): bool
    {
        return (bool)($this->options["foreignKey"] ?? false);
    }

    /**
     * Check if records returned by getting belongs-to/has-many are implicitly cached during the current request
     *
     * @return bool
     */
    public function isReusable(): bool
    {
        return $this->options["reusable"] ?? false;
    }

    /**
     * Check whether the relation is a 'many-to-many' relation or not
     *
     * @return bool
     */
    public function isThrough(): bool
    {
        return $this->type == self::HAS_ONE_THROUGH || $this->type == self::HAS_MANY_THROUGH;
    }

    /**
     * Sets the intermediate model data for has-*-through relations
     *
     * @param array|string $intermediateFields
     * @param string       $intermediateModel
     * @param array|string $intermediateReferencedFields
     *
     * @return void
     */
    public function setIntermediateRelation(
        array | string $intermediateFields,
        string $intermediateModel,
        array | string $intermediateReferencedFields
    ): void {
        $this->intermediateFields           = $intermediateFields;
        $this->intermediateModel            = $intermediateModel;
        $this->intermediateReferencedFields = $intermediateReferencedFields;
    }
}
