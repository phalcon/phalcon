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
use Phalcon\Paginator\Exceptions\BuilderModelNotDefined;
use Phalcon\Paginator\Exceptions\InvalidBuilderInstance;
use Phalcon\Paginator\Exceptions\MissingColumnsForHaving;
use Phalcon\Paginator\Exceptions\MissingRequiredParameter;
use Phalcon\Paginator\RepositoryInterface;

use function array_values;
use function ceil;
use function implode;
use function intval;
use function is_array;
use function is_string;
use function stripos;
use function strpos;
use function substr;
use function trim;

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
     * Column list used only for COUNT rewriting when the builder carries a
     * HAVING or GROUP BY clause. It supplies the columns for the subquery
     * that counts the grouped/having result set and is ignored otherwise.
     *
     * @var array|string|null
     */
    protected array | string | null $columns = null;

    /**
     * Phalcon\Paginator\Adapter\QueryBuilder
     *
     * The `columns` option is not a projection for the paginated rows; it is
     * consumed solely by the total-count rewrite when the builder has a
     * HAVING or GROUP BY clause (it becomes the column list of the counting
     * subquery). It has no effect on plain queries.
     *
     * @param array $config = [
     *                      'limit' => 10,
     *                      'builder' => null,
     *                      'columns' => ''
     *                      ]
     */
    public function __construct(array $config)
    {
        if (!isset($config["limit"])) {
            throw new MissingRequiredParameter("limit");
        }

        if (!isset($config["builder"])) {
            throw new MissingRequiredParameter("builder");
        }

        $builder = $config["builder"];

        if (!($builder instanceof Builder)) {
            throw new InvalidBuilderInstance();
        }

        if (isset($config["columns"])) {
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
        return $this->page;
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
     * @return RepositoryInterface
     * @throws Exception
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
        $numberPage = $this->page;

        $number = $limit * ($numberPage - 1);

        /**
         * Set the limit clause avoiding negative offsets
         */
        if ($number < $limit) {
            $builder->limit($limit);
        } else {
            $builder->limit($limit, $number);
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

        $hasMultipleGroups = false;

        /**
         * Change the queried columns by a COUNT(*)
         */
        if ($hasHaving && !$hasGroup) {
            if (empty($columns)) {
                throw new MissingColumnsForHaving();
            }

            $totalBuilder->columns($columns);
        } else {
            $hasDistinct    = false;
            $builderColumns = $builder->getColumns();

            if (is_string($builderColumns) && stripos(trim($builderColumns), "DISTINCT ") === 0) {
                $distinctColumn = trim(substr(trim($builderColumns), 9));
                $hasDistinct    = true;

                if (strpos($distinctColumn, ",") !== false) {
                    $totalBuilder->columns(["DISTINCT " . $distinctColumn]);
                    $hasMultipleGroups = true;
                } else {
                    $totalBuilder->columns(
                        ["COUNT(DISTINCT " . $distinctColumn . ") AS [rowcount]"]
                    );
                }
            }

            if (!$hasDistinct) {
                $totalBuilder->columns("COUNT(*) [rowcount]");
            }
        }

        /**
         * Change 'COUNT()' parameters, when the query contains 'GROUP BY'
         */
        if ($hasGroup) {
            if (is_array($groups)) {
                $groupColumn       = implode(", ", $groups);
                $hasMultipleGroups = count($groups) > 1;
            } else {
                $groupColumn       = $groups;
                $hasMultipleGroups = false;
            }

            if (!$hasHaving) {
                if (!empty($columns)) {
                    $groupColumn       = $columns;
                    $hasMultipleGroups = false;
                }

                if ($hasMultipleGroups) {
                    /**
                     * Multiple GROUP BY columns: COUNT(DISTINCT col1, col2) is
                     * invalid in PostgreSQL. Use DISTINCT columns and wrap in a
                     * subquery (same strategy as hasHaving) to count groups.
                     */
                    $totalBuilder->groupBy(null)->columns(
                        [
                            "DISTINCT " . $groupColumn,
                        ]
                    );
                } else {
                    $totalBuilder->groupBy(null)->columns(
                        [
                            "COUNT(DISTINCT " . $groupColumn . ") AS [rowcount]",
                        ]
                    );
                }
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
        if ($hasHaving || $hasMultipleGroups) {
            $sql        = $totalQuery->getSql();
            $modelClass = $builder->getModels();

            if ($modelClass === null) {
                throw new BuilderModelNotDefined();
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

            $rowcount   = $row ? intval($row["rowcount"]) : 0;
            $totalPages = intval(ceil($rowcount / $limit));
        } else {
            $result     = $totalQuery->execute();
            $row        = $result->getFirst();
            $rowcount   = $row ? intval($row->rowcount) : 0;
            $totalPages = intval(ceil($rowcount / $limit));
        }

        $next = $totalPages;
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
     */
    public function setQueryBuilder(Builder $builder): static
    {
        $this->builder = $builder;

        return $this;
    }
}
