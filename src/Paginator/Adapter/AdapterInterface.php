<?php

/*
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

/**
 * Phalcon\Paginator\AdapterInterface
 * Interface for Phalcon\Paginator adapters
 */
interface AdapterInterface
{
    /**
     * Get current rows limit
     */
    public function getLimit(): int | null;

    /**
     * Returns a slice of the resultset to show in the pagination
     */
    public function paginate(): RepositoryInterface;

    /**
     * Set the current page number
     */
    public function setCurrentPage(int $page);

    /**
     * Set current rows limit
     */
    public function setLimit(int $limit);
}
