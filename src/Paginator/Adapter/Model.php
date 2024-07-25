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

use Phalcon\Paginator\RepositoryInterface;

use function call_user_func;
use function is_array;
use function is_object;

/**
 * This adapter allows to paginate data using a Phalcon\Mvc\Model resultset as a
 * base.
 *
 * ```php
 * use Phalcon\Paginator\Adapter\Model;
 *
 * $paginator = new Model(
 *     [
 *         "model" => Robots::class,
 *         "limit" => 25,
 *         "page"  => $currentPage,
 *     ]
 * );
 *
 *
 * $paginator = new Model(
 *     [
 *         "model" => Robots::class,
 *         "parameters" => [
 *              "columns" => "id, name"
 *         ],
 *         "limit" => 12,
 *         "page"  => $currentPage,
 *     ]
 * );
 *
 *
 * $paginator = new Model(
 *     [
 *         "model" => Robots::class,
 *         "parameters" => [
 *              "type = :type:",
 *              "bind" => [
 *                  "type" => "mechanical"
 *              ],
 *              "order" => "name"
 *         ],
 *         "limit" => 16,
 *         "page"  => $currentPage,
 *     ]
 * );
 *
 * $paginator = new Model(
 *     [
 *         "model" => Robots::class,
 *         "parameters" => "(id % 2) = 0",
 *         "limit" => 8,
 *         "page"  => $currentPage,
 *     ]
 * );
 *
 *
 * $paginator = new Model(
 *     [
 *         "model" => Robots::class,
 *         "parameters" => [ "(id % 2) = 0" ],
 *         "limit" => 8,
 *         "page"  => $currentPage,
 *     ]
 * );
 *
 * $paginate = $paginator->paginate();
 *```
 */
class Model extends AbstractAdapter
{
    /**
     * Returns a slice of the resultset to show in the pagination
     *
     * @return RepositoryInterface
     */
    public function paginate(): RepositoryInterface
    {
        $pageItems  = [];
        $limit      = $this->limitRows;
        $pageNumber = $this->page;
        $modelClass = $this->config["model"];

        $parameters = $this->config["parameters"] ?? [];

        if (!is_array($parameters)) {
            $parameters = (array)$parameters;
        }

        // This can return int or ResultsetInterface if it's grouped
        $rowCountResult = call_user_func([$modelClass, "count"], $parameters);

        if (is_object($rowCountResult)) {
            $rowcount = (int)$rowCountResult->count();
        } else {
            $rowcount = (int)$rowCountResult;
        }

        if ($rowcount % $limit !== 0) {
            $totalPages = (int)($rowcount / $limit + 1);
        } else {
            $totalPages = (int)($rowcount / $limit);
        }

        if ($rowcount > 0) {
            $parameters["limit"]  = $limit;
            $parameters["offset"] = $limit * ($pageNumber - 1);

            $pageItems = call_user_func(
                [$modelClass, "find"],
                $parameters
            );
        }

        // Fix next
        $next = $pageNumber + 1;
        if ($next > $totalPages) {
            $next = $totalPages;
        }

        $previous = 1;
        if ($pageNumber > 1) {
            $previous = $pageNumber - 1;
        }

        return $this->getRepository(
            [
                RepositoryInterface::PROPERTY_ITEMS         => $pageItems,
                RepositoryInterface::PROPERTY_TOTAL_ITEMS   => $rowcount,
                RepositoryInterface::PROPERTY_LIMIT         => $this->limitRows,
                RepositoryInterface::PROPERTY_FIRST_PAGE    => 1,
                RepositoryInterface::PROPERTY_PREVIOUS_PAGE => $previous,
                RepositoryInterface::PROPERTY_CURRENT_PAGE  => $pageNumber,
                RepositoryInterface::PROPERTY_NEXT_PAGE     => $next,
                RepositoryInterface::PROPERTY_LAST_PAGE     => $totalPages,
            ]
        );
    }
}
