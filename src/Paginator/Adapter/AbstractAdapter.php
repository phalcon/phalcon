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

use Phalcon\Contracts\Paginator\PaginatorTypes;
use Phalcon\Paginator\Exception;
use Phalcon\Paginator\Exceptions\InvalidLimit;
use Phalcon\Paginator\Exceptions\MissingRequiredParameter;
use Phalcon\Paginator\Repository;
use Phalcon\Paginator\RepositoryInterface;

/**
 * Phalcon\Paginator\Adapter\AbstractAdapter
 *
 * @phpstan-import-type paginator_config from PaginatorTypes
 * @phpstan-import-type paginator_properties from PaginatorTypes
 */
abstract class AbstractAdapter implements AdapterInterface
{
    /**
     * Number of rows to show in the paginator. By default is null
     *
     * @var int|null
     */
    protected int | null $limitRows = null;

    /**
     * Current page in paginate
     *
     * @var int|null
     */
    protected int | null $page = null;

    /**
     * Repository for pagination
     */
    protected RepositoryInterface $repository;

    /**
     * Constructor
     *
     * @param paginator_config $config
     *
     * @throws Exception
     */
    public function __construct(
        protected array $config
    ) {
        $this->repository = new Repository();

        if (!isset($config["limit"])) {
            throw new MissingRequiredParameter("limit");
        }

        /** @var int $limit */
        $limit = $config["limit"];

        $this->setLimit($limit);

        if (isset($config["page"])) {
            /** @var int $page */
            $page = $config["page"];

            $this->setCurrentPage($page);
        }

        if (isset($config["repository"])) {
            /** @var RepositoryInterface $repository */
            $repository = $config["repository"];

            $this->setRepository($repository);
        }
    }

    /**
     * Get current rows limit
     */
    public function getLimit(): int
    {
        /** @var int $limitRows */
        $limitRows = $this->limitRows;

        return $limitRows;
    }

    /**
     * Set the current page number
     */
    public function setCurrentPage(int $page): AdapterInterface
    {
        $this->page = $page;

        return $this;
    }

    /**
     * Set current rows limit
     */
    public function setLimit(int $limit): AdapterInterface
    {
        if ($limit <= 0) {
            throw new InvalidLimit();
        }

        $this->limitRows = $limit;

        return $this;
    }

    /**
     * Sets current repository for pagination
     */
    public function setRepository(RepositoryInterface $repository): AdapterInterface
    {
        $this->repository = $repository;

        return $this;
    }

    /**
     * Gets current repository for pagination
     *
     * @param paginator_properties|null $properties
     */
    protected function getRepository(
        array | null $properties = null
    ): RepositoryInterface {
        if (null !== $properties) {
            $this->repository->setProperties($properties);
        }

        return $this->repository;
    }
}
