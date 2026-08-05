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
use Phalcon\Paginator\Exceptions\InvalidLimit;
use Phalcon\Paginator\Exceptions\MissingRequiredParameter;
use Phalcon\Paginator\Repository;
use Phalcon\Paginator\RepositoryInterface;

/**
 * Phalcon\Paginator\Adapter\AbstractAdapter
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
     * @param array $config
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

        $this->setLimit($config["limit"]);

        if (isset($config["page"])) {
            $this->setCurrentPage($config["page"]);
        }

        if (isset($config["repository"])) {
            $this->setRepository($config["repository"]);
        }
    }

    /**
     * Get current rows limit
     */
    public function getLimit(): int
    {
        return $this->limitRows;
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
