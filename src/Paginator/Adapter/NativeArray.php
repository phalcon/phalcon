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

use Phalcon\Paginator\Exception;
use Phalcon\Paginator\RepositoryInterface;

/**
 * Pagination using a PHP array as source of data
 *
 * ```php
 * use Phalcon\Paginator\Adapter\NativeArray;
 *
 * $paginator = new NativeArray(
 *     [
 *         "data"  => [
 *             ["id" => 1, "name" => "Artichoke"],
 *             ["id" => 2, "name" => "Carrots"],
 *             ["id" => 3, "name" => "Beet"],
 *             ["id" => 4, "name" => "Lettuce"],
 *             ["id" => 5, "name" => ""],
 *         ],
 *         "limit" => 2,
 *         "page"  => $currentPage,
 *     ]
 * );
 *```
 */
class NativeArray extends AbstractAdapter
{
    /**
     * Returns a slice of the resultset to show in the pagination
     *
     * @throws Exception
     * @return RepositoryInterface
     */
    public function paginate(): RepositoryInterface
    {
        /**
         * TODO: Rewrite the whole method!
         */
        $config = $this->config;
        $items  = $config["data"];

        if (!is_array($items)) {
            throw new Exception("Invalid data for paginator");
        }

        $show       = (int)$this->limitRows;
        $pageNumber = (int)$this->page;

        if ($pageNumber <= 0) {
            $pageNumber = 1;
        }

        $number       = count($items);
        $roundedTotal = $number / floatval($show);
        $totalPages   = (int)$roundedTotal;

        /**
         * Increase total pages if wasn't integer
         */
        if ($totalPages != $roundedTotal) {
            $totalPages++;
        }

        $items = array_slice(
            $items,
            $show * ($pageNumber - 1),
            $show
        );

        // Fix next
        $next = $totalPages;
        if ($pageNumber < $totalPages) {
            $next = $pageNumber + 1;
        }

        $previous = 1;
        if ($pageNumber > 1) {
            $previous = $pageNumber - 1;
        }

        return $this->getRepository(
            [
                RepositoryInterface::PROPERTY_ITEMS         => $items,
                RepositoryInterface::PROPERTY_TOTAL_ITEMS   => $number,
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
