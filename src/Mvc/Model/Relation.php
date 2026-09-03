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

use function call_user_func;
use function is_callable;

/**
 * This class represents a relationship between two models
 *
 * @phpstan-import-type mvc_model_parameters from MvcTypes
 * @phpstan-import-type mvc_relation_fields from MvcTypes
 * @phpstan-import-type mvc_relation_options from MvcTypes
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
     *
     * @phpstan-var mvc_relation_fields
     */
    protected array | string $intermediateFields;

    /**
     * @var string|null
     */
    protected string | null $intermediateModel = null;

    /**
     * @var array|string
     *
     * @phpstan-var mvc_relation_fields
     */
    protected array | string $intermediateReferencedFields;

    /**
     * Phalcon\Mvc\Model\Relation constructor
     *
     * @param int          $type
     * @param string       $referencedModel
     * @param mixed $fields
     * @param mixed $referencedFields
     * @param array        $options
     *
     * @phpstan-param mvc_relation_options $options
     */
    public function __construct(
        protected int $type,
        protected string $referencedModel,
        protected mixed $fields,
        protected mixed $referencedFields,
        protected array $options = []
    ) {
    }

    /**
     * Returns the fields
     *
     * @return array|string
     *
     * @phpstan-return mvc_relation_fields
     */
    public function getFields()
    {
        // The constructor receives the fields as a string or an array of strings.
        /** @var mvc_relation_fields */
        return $this->fields;
    }

    /**
     * Returns the foreign key configuration
     *
     * @return array|bool|string
     *
     * @phpstan-return array<string, mixed>|string|bool
     */
    public function getForeignKey()
    {
        if (isset($this->options["foreignKey"]) && !empty($this->options["foreignKey"])) {
            /** @var array<string, mixed>|bool|string */
            return $this->options["foreignKey"];
        }

        return false;
    }

    /**
     * Gets the intermediate fields for has-*-through relations
     *
     * @return array|string
     *
     * @phpstan-return mvc_relation_fields
     */
    public function getIntermediateFields()
    {
        return $this->intermediateFields;
    }

    /**
     * Gets the intermediate model for has-*-through relations
     */
    public function getIntermediateModel(): string
    {
        // setIntermediateRelation() sets the model of a has-*-through relation.
        /** @var string */
        return $this->intermediateModel;
    }

    /**
     * Gets the intermediate referenced fields for has-*-through relations
     *
     * @return array|string
     *
     * @phpstan-return mvc_relation_fields
     */
    public function getIntermediateReferencedFields()
    {
        return $this->intermediateReferencedFields;
    }

    /**
     * Returns an option by the specified name
     * If the option does not exist null is returned
     *
     * @param string $name
     *
     * @return mixed
     */
    public function getOption(string $name)
    {
        return $this->options[$name] ?? null;
    }

    /**
     * Returns the options
     *
     * @return array
     *
     * @phpstan-return mvc_relation_options
     */
    public function getOptions(): array
    {
        return $this->options;
    }

    /**
     * Returns parameters that must be always used when the related records are obtained
     *
     * @return array|false
     *
     * @phpstan-return mvc_model_parameters|false
     */
    public function getParams()
    {
        if (
            isset($this->options["params"]) &&
            !empty($this->options["params"])
        ) {
            if (is_callable($this->options["params"])) {
                /** @var false|mvc_model_parameters */
                return call_user_func($this->options["params"]);
            }

            /** @var false|mvc_model_parameters */
            return $this->options["params"];
        }

        return false;
    }

    /**
     * Returns the referenced fields
     *
     * @return array|string
     *
     * @phpstan-return mvc_relation_fields
     */
    public function getReferencedFields()
    {
        // The constructor receives the referenced fields as a string or an array of strings.
        /** @var mvc_relation_fields */
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
        // The "reusable" option holds a boolean flag.
        /** @var bool */
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
     * @param mixed $intermediateFields
     * @param string       $intermediateModel
     * @param mixed $intermediateReferencedFields
     *
     * @return void
     *
     */
    public function setIntermediateRelation(
        mixed $intermediateFields,
        string $intermediateModel,
        mixed $intermediateReferencedFields
    ) {
        // The manager passes the intermediate fields as a string or an array of strings.
        /**
         * @var mvc_relation_fields $intermediateFields
         * @var mvc_relation_fields $intermediateReferencedFields
         */
        $this->intermediateFields           = $intermediateFields;
        $this->intermediateModel            = $intermediateModel;
        $this->intermediateReferencedFields = $intermediateReferencedFields;
    }
}
