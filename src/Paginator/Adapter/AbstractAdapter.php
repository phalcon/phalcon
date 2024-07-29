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
use Phalcon\Paginator\Repository;
use Phalcon\Paginator\RepositoryInterface;

/**
 * Phalcon\Paginator\Adapter\AbstractAdapter
 */
abstract class AbstractAdapter implements AdapterInterface
{
    /**
     * Configuration of paginator
     *
     * @var array
     */
    protected array $config;

    /**
     * Number of rows to show in the paginator. By default is null
     *
     * @var int|null
     */
    protected ?int $limitRows = null;

    /**
     * Current page in paginate
     *
     * @var int|null
     */
    protected ?int $page = null;

    /**
     * Repository for pagination
     *
     * @var RepositoryInterface|null
     */
    protected ?RepositoryInterface $repository = null;

    /**
     * Phalcon\Paginator\Adapter\AbstractAdapter constructor
     *
     * @param array $config
     *
     * @throws Exception
     */
    public function __construct(array $config)
    {
        $this->repository = new Repository();
        $this->config = $config;

        if (isset($config["limit"])) {
            $this->setLimit(
                $config["limit"]
            );
        }

        if (isset($config["page"])) {
            $this->setCurrentPage(
                $config["page"]
            );
        }

        if (isset($config["repository"])) {
            $this->setRepository(
                $config["repository"]
            );
        }
    }

    /**
     * Get current rows limit
     *
     * @return int|null
     */
    public function getLimit(): int | null
    {
        return $this->limitRows;
    }

    /**
     * Set the current page number
     *
     * @param int $page
     *
     * @return AdapterInterface
     */
    public function setCurrentPage(int $page): AdapterInterface
    {
        $this->page = $page;

        return $this;
    }

    /**
     * Set current rows limit
     *
     * @param int $limit
     *
     * @throws Exception
     * @return AdapterInterface
     * @throws Exception
     */
    public function setLimit(int $limit): AdapterInterface
    {
        if ($limit <= 0) {
            throw new Exception("Limit must be greater than zero");
        }

        $this->limitRows = $limit;

        return $this;
    }

    /**
     * Sets current repository for pagination
     *
     * @param RepositoryInterface $repository
     *
     * @return AdapterInterface
     */
    public function setRepository(RepositoryInterface $repository): AdapterInterface
    {
        $this->repository = $repository;

        return $this;
    }

    /**
     * Gets current repository for pagination
     *
     * @param array|null $properties
     *
     * @return RepositoryInterface
     */
    protected function getRepository(array $properties = null): RepositoryInterface
    {
        if (null !== $properties) {
            $this->repository->setProperties($properties);
        }

        return $this->repository;
    }
}
