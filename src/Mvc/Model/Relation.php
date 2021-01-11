<?php
/**
 * This file is part of the Phalcon Framework.
 *
 * (c) Phalcon Team <team@phalcon.io>
 *
 * For the full copyright and license information, please view the LICENSE.txt
 * file that was distributed with this source code.
 */

namespace Phalcon\Mvc\Model;

/**
 * Phalcon\Mvc\Model\Relation
 *
 * This class represents a relationship between two models
 */
class Relation implements RelationInterface
{
    const ACTION_CASCADE   = 2;
    const ACTION_RESTRICT  = 1;
    const BELONGS_TO       = 0;
    const HAS_MANY         = 2;
    const HAS_MANY_THROUGH = 4;
    const HAS_ONE          = 1;
    const HAS_ONE_THROUGH  = 3;
    const NO_ACTION        = 0;

    protected $fields;
    protected $intermediateFields;
    protected string $intermediateModel;
    protected $intermediateReferencedFields;
    protected array $options;
    protected $referencedFields;
    protected string $referencedModel;
    protected int $type;

    /**
     * Phalcon\Mvc\Model\Relation constructor
     *
     * @param string|array fields
     * @param string|array referencedFields
     */
    public function __construct(int $type, string $referencedModel, $fields, 
    	$referencedFields, array $options = [])
    {
            $this->type = $type;
            $this->referencedModel = $referencedModel;
            $this->fields = $fields;
            $this->referencedFields = $referencedFields;
            $this->options = $options;
    }

    /**
     * Returns the fields
     *
     * @return string|array
     */
    public function getFields() 
    {
        return $this->fields;
    }

    /**
     * Returns the foreign key configuration
     *
     * @return string|array|bool
     */
    public function getForeignKey()
    {
        return $this->option["foreignKey"] ?? false;
    }

    /**
     * Gets the intermediate fields for has-*-through relations
     *
     * @return string|array
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
        return $this->intermediateModel;
    }

    /**
     * Gets the intermediate referenced fields for has-*-through relations
     *
     * @return string|array
     */
    public function getIntermediateReferencedFields()
    {
        return $this->intermediateReferencedFields;
    }

    /**
     * Returns an option by the specified name
     * If the option doesn't exist null is returned
     */
    public function getOption(string $name)
    {
    	return $this->options[$name] ?? null;
    }

    /**
     * Returns the options
     */
    public function getOptions() : array
    {
        return $this->options;
    }

    /**
     * Returns parameters that must be always used when the related records are obtained
     *
     * @return array|bool
     */
    public function getParams()
    {

    	$params = $this->options["params"] ?? null;
    	if ($params===null) {
    		return false;
    	}
    	if (is_callable($params)) {
    		return call_user_func($params);
    	}
    	return $params;
    }

    /**
     * Returns the relation type
     */
    public function getType() : int
    {
        return $this->type;
    }

    /**
     * Returns the referenced fields
     *
     * @return string|array
     */
    public function getReferencedFields()
    {
        return $this->referencedFields;
    }

    /**
     * Returns the referenced model
     */
    public function getReferencedModel() : string
    {
        return $this->referencedModel;
    }

    /**
     * Check whether the relation act as a foreign key
     */
    public function isForeignKey() : bool
    {
    	return $this->options["foreignKey"] ?? false;
    }

    /**
     * Check whether the relation is a 'many-to-many' relation or not
     */
    public function isThrough() : bool
    {
		$type = $this->type;
        return ($type === self::HAS_ONE_THROUGH) || ($type === self::HAS_MANY_THROUGH);
    }

    /**
     * Check if records returned by getting belongs-to/has-many are implicitly cached during the current request
     */
    public function isReusable() : bool
    {
   		return $this->options["reusable"] ?? false;
    }

    /**
     * Sets the intermediate model data for has-*-through relations
     *
     * @param string|array 		 intermediateFields
     * @param string 			 intermediateModel
     * @param string|array       intermediateReferencedFields
     */
    public function setIntermediateRelation($intermediateFields, 
    		string $intermediateModel, $intermediateReferencedFields) : void
    {
            $this->intermediateFields = $intermediateFields;
            $this->intermediateModel = $intermediateModel;
            $this->intermediateReferencedFields = $intermediateReferencedFields;
    }
}
