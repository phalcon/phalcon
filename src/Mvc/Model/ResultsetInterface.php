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

use Closure;
use Phalcon\Messages\MessageInterface;
use Phalcon\Mvc\ModelInterface;

/**
 * Interface for Phalcon\Mvc\Model\Resultset
 */
interface ResultsetInterface
{
    /**
     * Deletes every record in the resultset
     */
    public function delete(Closure | null $conditionCallback = null): bool;

    /**
     * Filters a resultset returning only those the developer requires
     *
     *```php
     * $filtered = $invoices->filter(
     *     function ($invoice) {
     *         if ($invoice->inv_id < 3) {
     *             return $invoice;
     *         }
     *     }
     * );
     *```
     *
     * @return ModelInterface[]
     */
    public function filter(mixed $filter): array;

    /**
     * Returns the associated cache for the resultset
     *
     * @return mixed
     */
    public function getCache();

    /**
     * Get first row in the resultset
     *
     * @return mixed
     */
    public function getFirst();

    /**
     * Returns the current hydration mode
     */
    public function getHydrateMode(): int;

    /**
     * Get last row in the resultset
     */
    public function getLast(): \Phalcon\Mvc\Model\Row | \Phalcon\Mvc\ModelInterface | null;

    /**
     * Returns the error messages produced by a batch operation
     *
     * @return MessageInterface[]
     */
    public function getMessages(): array;

    /**
     * Returns the internal type of data retrieval that the resultset is using
     */
    public function getType(): int;

    /**
     * Tell if the resultset is fresh or an old one cached
     */
    public function isFresh(): bool;

    /**
     * Sets the hydration mode in the resultset
     */
    public function setHydrateMode(int $hydrateMode): ResultsetInterface;

    /**
     * Set if the resultset is fresh or an old one cached
     */
    public function setIsFresh(bool $isFresh): ResultsetInterface;

    /**
     * Returns a complete resultset as an array, if the resultset has a big
     * number of rows it could consume more memory than currently it does.
     *
     * @phpstan-return array<array-key, mixed>
     */
    public function toArray(): array;

    /**
     * Updates every record in the resultset
     */
    public function update(
        mixed $data,
        Closure | null $conditionCallback = null
    ): bool;
}
