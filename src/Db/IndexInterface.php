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

namespace Phalcon\Db;

/**
 * Interface for Phalcon\Db\Index
 */
interface IndexInterface
{
    /**
     * Gets the columns that corresponds the index
     *
     * @return array
     */
    public function getColumns(): array;

    /**
     * Gets the index name
     *
     * @return string
     */
    public function getName(): string;

    /**
     * Gets the index type
     *
     * @return string
     */
    public function getType(): string;
}
