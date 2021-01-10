<?php
/**
 * This file is part of the Phalcon Framework.
 *
 * (c) Phalcon Team <team@phalcon.io>
 *
 * For the full copyright and license information, please view the
 * LICENSE.txt file that was distributed with this source code.
 */

namespace Phiz\Mvc\Model;

use Closure;
use Phiz\Mvc\ModelInterface;
use Phiz\Cache\Adapter\AdapterInterface;

/**
 * Phiz\Mvc\Model\ResultsetInterface
 *
 * Interface for Phiz\Mvc\Model\Resultset
 */
interface ResultsetInterface
{
    /**
     * Deletes every record in the resultset
     */
    public function delete(Closure $conditionCallback = null) : bool;

    /**
     * Filters a resultset returning only those the developer requires
     *
     *```php
     * $filtered = $robots->filter(
     *     function ($robot) {
     *         if ($robot->id < 3) {
     *             return $robot;
     *         }
     *     }
     * );
     *```
     */
    public function filter(callable $filter) : array;

    /**
     * Returns the associated cache for the resultset
     */
    public function getCache() : AdapterInterface;

    /**
     * Get first row in the resultset
     *
     * @return ModelInterface|Row|null
     */
    public function getFirst() : mixed;

    /**
     * Returns the current hydration mode
     */
    public function getHydrateMode() : int;

    /**
     * Get last row in the resultset
     */
    public function getLast() : ?ModelInterface;

    /**
     * Returns the error messages produced by a batch operation
     */
    public function getMessages() : array;

    /**
     * Returns the internal type of data retrieval that the resultset is using
     */
    public function getType() : int;

    /**
     * Tell if the resultset if fresh or an old one cached
     */
    public function isFresh() : bool;

    /**
     * Sets the hydration mode in the resultset
     */
    public function setHydrateMode(int $hydrateMode) : ResultsetInterface;

    /**
     * Set if the resultset is fresh or an old one cached
     */
    public function setIsFresh(bool $isFresh) : ResultsetInterface;

    /**
     * Returns a complete resultset as an array, if the resultset has a big number of rows
     * it could consume more memory than currently it does.
     */
    public function toArray() : array;

    /**
     * Updates every record in the resultset
     *
     * @param array data
     */
    public function update($data, Closure $conditionCallback = null) : bool;
}
