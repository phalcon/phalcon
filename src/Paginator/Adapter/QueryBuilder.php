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

use Phalcon\Db\Enum;
use Phalcon\Mvc\Model\Query\Builder;
use Phalcon\Paginator\Exception;
use Phalcon\Paginator\RepositoryInterface;

use function array_values;
use function ceil;
use function implode;
use function intval;
use function is_array;

/**
 * Pagination using a PHQL query builder as source of data
 *
 * ```php
 * use Phalcon\Paginator\Adapter\QueryBuilder;
 *
 * $builder = $this->modelsManager->createBuilder()
 *                 ->columns("id, name")
 *                 ->from(Robots::class)
 *                 ->orderBy("name");
 *
 * $paginator = new QueryBuilder(
 *     [
 *         "builder" => $builder,
 *         "limit"   => 20,
 *         "page"    => 1,
 *     ]
 * );
 *```
 */
class QueryBuilder extends AbstractAdapter
{
    /**
     * Paginator's data
     *
     * @var Builder
     */
    protected Builder $builder;

    /**
     * Columns for count query if builder has having
     *
     * @var array|string|null
     */
    protected array | string | null $columns = null;

    /**
     * Phalcon\Paginator\Adapter\QueryBuilder
     *
     * @param array $config = [
     *     'limit' => 10,
     *     'builder' => null,
     *     'columns' => ''
     * ]
     *
     * @throws Exception
     */
    public function __construct(array $config)
    {
        if (!isset($config["limit"])) {
            throw new Exception("Parameter 'limit' is required");
        }

        if (!isset($config["builder"])) {
            throw new Exception("Parameter 'builder' is required");
        }
        if ($config['builder'] instanceof Builder === false) {
            throw new Exception(
                "Parameter 'builder' must be an instance " .
                "of Phalcon\\Mvc\\Model\\Query\\Builder"
            );
        }
        $builder = $config['builder'];

        if (
            isset($config["columns"]) &&
            (is_array($config['columns']) || is_string($config['columns']))
        ) {
            $this->columns = $config["columns"];
        }

        parent::__construct($config);

        $this->setQueryBuilder($builder);
    }

    /**
     * Get the current page number
     *
     * @return int
     */
    public function getCurrentPage(): int
    {
        return (int)$this->page;
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
     * @throws Exception
     * @return RepositoryInterface
     */
    public function paginate(): RepositoryInterface
    {
        $originalBuilder = $this->builder;
        $columns         = $this->columns;

        /**
         * We make a copy of the original builder to leave it as it is
         */
        $builder = clone $originalBuilder;

        /**
         * We make a copy of the original builder to count the total of records
         */
        $totalBuilder = clone $builder;

        $limit      = $this->limitRows;
        $numberPage = (int)$this->page;

        if (!$numberPage) {
            $numberPage = 1;
        }

        $number = $limit * ($numberPage - 1);

        /**
         * Set the limit clause avoiding negative offsets
         */
        if ($number < $limit) {
            $builder->limit((int)$limit);
        } else {
            $builder->limit((int)$limit, $number);
        }

        $query = $builder->getQuery();

        $previous = 1;
        if ($numberPage !== 1) {
            $previous = $numberPage - 1;
        }

        /**
         * Execute the query an return the requested slice of data
         */
        $items     = $query->execute();
        $hasHaving = !empty($totalBuilder->getHaving());
        $groups    = $totalBuilder->getGroupBy();
        $hasGroup  = !empty($groups);

        /**
         * Change the queried columns by a COUNT(*)
         */

        if ($hasHaving && !$hasGroup) {
            if (empty($columns)) {
                throw new Exception(
                    "When having is set there should be columns "
                    . "option provided for which calculate row count"
                );
            }

            $totalBuilder->columns($columns);
        } else {
            $totalBuilder->columns("COUNT(*) [rowcount]");
        }

        /**
         * Change 'COUNT()' parameters, when the query contains 'GROUP BY'
         */
        if ($hasGroup) {
            $groupColumn = implode(", ", $groups);

            if (!$hasHaving) {
                $totalBuilder->groupBy('')->columns(
                    [
                        "COUNT(DISTINCT " . $groupColumn . ") AS [rowcount]",
                    ]
                );
            } else {
                $totalBuilder->columns(
                    [
                        "DISTINCT " . $groupColumn,
                    ]
                );
            }
        }

        /**
         * Remove the 'ORDER BY' clause, PostgreSQL requires this
         */
        $totalBuilder->orderBy(null);

        /**
         * Obtain the PHQL for the total query
         */
        $totalQuery = $totalBuilder->getQuery();

        /**
         * Obtain the result of the total query
         * If we have having perform native count on temp table
         */
        if ($hasHaving) {
            $sql        = $totalQuery->getSql();
            $modelClass = $builder->getModels();

            if ($modelClass === null) {
                throw new Exception("Model not defined in builder");
            }

            if (is_array($modelClass)) {
                $modelClass = array_values($modelClass)[0];
            }

            $model     = new $modelClass();
            $dbService = $model->getReadConnectionService();
            $db        = $totalBuilder->getDI()->get($dbService);

            $row = $db->fetchOne(
                "SELECT COUNT(*) as \"rowcount\" FROM (" . $sql["sql"] . ") as T1",
                Enum::FETCH_ASSOC,
                $sql["bind"]
            );

            $rowcount = $row ? intval($row["rowcount"]) : 0;
        } else {
            $result   = $totalQuery->execute();
            $row      = $result->getFirst();
            $rowcount = $row ? intval($row->rowcount) : 0;
        }

        $totalPages = intval(ceil($rowcount / $limit));
        $next       = $totalPages;
        if ($numberPage < $totalPages) {
            $next = $numberPage + 1;
        }

        return $this->getRepository(
            [
                RepositoryInterface::PROPERTY_ITEMS         => $items,
                RepositoryInterface::PROPERTY_TOTAL_ITEMS   => $rowcount,
                RepositoryInterface::PROPERTY_LIMIT         => $this->limitRows,
                RepositoryInterface::PROPERTY_FIRST_PAGE    => 1,
                RepositoryInterface::PROPERTY_PREVIOUS_PAGE => $previous,
                RepositoryInterface::PROPERTY_CURRENT_PAGE  => $numberPage,
                RepositoryInterface::PROPERTY_NEXT_PAGE     => $next,
                RepositoryInterface::PROPERTY_LAST_PAGE     => $totalPages,
            ]
        );
    }

    /**
     * Set query builder object
     *
     * @param Builder $builder
     *
     * @return QueryBuilder
     */
    public function setQueryBuilder(Builder $builder): QueryBuilder
    {
        $this->builder = $builder;

        return $this;
    }
}
