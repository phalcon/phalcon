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

namespace Phalcon\Paginator\Adapter;

use Phalcon\Mvc\Model\Query\Builder;
use Phalcon\Paginator\Exception;
use Phalcon\Paginator\RepositoryInterface;

use function array_column;
use function array_pop;
use function count;
use function is_string;

/**
 * Cursor-based (keyset) pagination using a PHQL query builder as source of
 * data.
 *
 * Unlike offset pagination, this adapter does not use an ever-growing OFFSET.
 * It appends a WHERE condition on a unique, indexed cursor column so that each
 * page is an O(1) index seek regardless of depth.
 *
 * Limitations:
 * - No total count: `getTotalItems()` always returns 0.
 * - No random access: `getLast()` always returns 0. Pages must be traversed
 *   in order by following the cursor value returned in `getNext()`.
 * - The cursor column must be unique and indexed (e.g. a primary key).
 * - Items are returned as an array of associative arrays (via
 *   `Resultset::toArray()`), not as model objects.
 * - `cursorColumn` must match the PHQL-accessible column name exactly
 *   (e.g. `"inv_id"`).
 *
 * ```php
 * use Phalcon\Paginator\Adapter\QueryBuilderCursor;
 *
 * $builder = $this->modelsManager->createBuilder()
 *                 ->columns("inv_id, inv_title")
 *                 ->from(Invoices::class)
 *                 ->orderBy("inv_id");
 *
 * $paginator = new QueryBuilderCursor(
 *     [
 *         "builder"      => $builder,
 *         "limit"        => 20,
 *         "cursorColumn" => "inv_id",
 *         "cursor"       => null,  // first page; pass $page->getNext() for subsequent pages
 *     ]
 * );
 *
 * $page = $paginator->paginate();
 * // $page->getItems()   — array of rows for this page
 * // $page->getNext()    — cursor value to pass for the next page (0 means no more pages)
 * // $page->getCurrent() — cursor value used for this page (0 on first page)
 * ```
 */
class QueryBuilderCursor extends AbstractAdapter
{
    /**
     * Paginator's data
     *
     * @var Builder
     */
    protected Builder $builder;

    /**
     * The cursor value for the current page (null = first page)
     *
     * @var int|null
     */
    protected int | null $cursor = null;

    /**
     * The column used as the cursor (must be unique and indexed)
     *
     * @var string
     */
    protected string $cursorColumn;

    /**
     * Phalcon\Paginator\Adapter\QueryBuilderCursor
     *
     * @param array $config = [
     *     'limit'        => 10,
     *     'builder'      => null,
     *     'cursorColumn' => 'id',
     *     'cursor'       => null
     * ]
     */
    public function __construct(array $config)
    {
        if (!isset($config["limit"])) {
            throw new Exception("Parameter 'limit' is required");
        }

        if (!isset($config["builder"])) {
            throw new Exception("Parameter 'builder' is required");
        }

        $builder = $config["builder"];

        if (!($builder instanceof Builder)) {
            throw new Exception(
                "Parameter 'builder' must be an instance " .
                "of Phalcon\\Mvc\\Model\\Query\\Builder"
            );
        }

        if (!isset($config["cursorColumn"])) {
            throw new Exception("Parameter 'cursorColumn' is required");
        }

        $cursorColumn = $config["cursorColumn"];

        if (!is_string($cursorColumn) || $cursorColumn === '') {
            throw new Exception(
                "Parameter 'cursorColumn' must be a non-empty string"
            );
        }

        $this->cursorColumn = $cursorColumn;

        if (isset($config["cursor"])) {
            $this->cursor = $config["cursor"];
        }

        parent::__construct($config);

        $this->setQueryBuilder($builder);
    }

    /**
     * Get the cursor value for the current page (null on first page)
     *
     * @return int|null
     */
    public function getCursor(): int | null
    {
        return $this->cursor;
    }

    /**
     * Get the cursor column name
     *
     * @return string
     */
    public function getCursorColumn(): string
    {
        return $this->cursorColumn;
    }

    /**
     * Get the current page number
     *
     * Returns the cursor value used for this page, or 0 for the first page.
     * Use getCursor() to retrieve the raw cursor value.
     *
     * @return int
     */
    public function getCurrentPage(): int
    {
        if ($this->cursor === null) {
            return 0;
        }

        return $this->cursor;
    }

    /**
     * Get query builder object
     *
     * @return Builder
     */
    public function getQueryBuilder(): Builder
    {
        return $this->builder;
    }

    /**
     * Returns a slice of the resultset to show in the pagination
     *
     * Fetches `limit + 1` rows from the builder. If the extra row is present
     * a next page exists; it is discarded and the cursor value of the last
     * included row is stored in the `next` repository property.
     *
     * @return RepositoryInterface
     */
    public function paginate(): RepositoryInterface
    {
        $builder       = clone $this->builder;
        $limit         = (int) $this->limitRows;
        $currentCursor = $this->cursor;
        $currentPage   = ($currentCursor === null) ? 0 : $currentCursor;

        if ($currentCursor !== null) {
            $builder->andWhere(
                "[" . $this->cursorColumn . "] > :cursor:",
                ["cursor" => $currentCursor]
            );
        }

        $builder->limit($limit + 1);

        $query  = $builder->getQuery();
        $result = $query->execute();
        $items  = $result->toArray();

        if (count($items) > $limit) {
            array_pop($items);

            $lastItem   = $items[count($items) - 1];
            $nextCursor = (int) $lastItem[$this->cursorColumn];
        } else {
            $nextCursor = 0;
        }

        return $this->getRepository(
            [
                RepositoryInterface::PROPERTY_ITEMS         => $items,
                RepositoryInterface::PROPERTY_TOTAL_ITEMS   => 0,
                RepositoryInterface::PROPERTY_LIMIT         => $this->limitRows,
                RepositoryInterface::PROPERTY_FIRST_PAGE    => 1,
                RepositoryInterface::PROPERTY_PREVIOUS_PAGE => 0,
                RepositoryInterface::PROPERTY_CURRENT_PAGE  => $currentPage,
                RepositoryInterface::PROPERTY_NEXT_PAGE     => $nextCursor,
                RepositoryInterface::PROPERTY_LAST_PAGE     => 0,
            ]
        );
    }

    /**
     * Set the cursor value for the next paginate() call
     *
     * Pass the value returned by Repository::getNext() to advance to the
     * next page, or null to restart from the first page.
     *
     * @param int|null $cursor
     *
     * @return QueryBuilderCursor
     */
    public function setCursor(int | null $cursor): static
    {
        $this->cursor = $cursor;

        return $this;
    }

    /**
     * Set query builder object
     *
     * @param Builder $builder
     *
     * @return QueryBuilderCursor
     */
    public function setQueryBuilder(Builder $builder): static
    {
        $this->builder = $builder;

        return $this;
    }
}
