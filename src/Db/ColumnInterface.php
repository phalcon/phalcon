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
 * Interface for Phalcon\Db\Column
 */
interface ColumnInterface
{
    /**
     * Check whether field absolute to position in table
     *
     * @return string
     */
    public function getAfterPosition(): string;

    /**
     * Returns the type of bind handling
     *
     * @return int
     */
    public function getBindType(): int;

    /**
     * Column's comment
     *
     * @return string
     */
    public function getComment(): string;

    /**
     * Returns default value of column
     *
     * @return mixed
     */
    public function getDefault(): mixed;

    /**
     * Returns column name
     *
     * @return string
     */
    public function getName(): string;

    /**
     * Returns column scale
     *
     * @return int
     */
    public function getScale(): int;

    /**
     * Returns column size
     *
     * @return int|string
     */
    public function getSize(): int | string;

    /**
     * Returns column type
     *
     * @return int
     */
    public function getType(): int;

    /**
     * Returns column type reference
     *
     * @return int
     */
    public function getTypeReference(): int;

    /**
     * Returns column type values
     *
     * @return array|string
     */
    public function getTypeValues(): array | string;


    /**
     * Check whether column has default value
     *
     * @return bool
     */
    public function hasDefault(): bool;

    /**
     * Auto-Increment
     *
     * @return bool
     */
    public function isAutoIncrement(): bool;

    /**
     * Check whether the column is the first in table
     *
     * @return bool
     */
    public function isFirst(): bool;

    /**
     * Not null
     *
     * @return bool
     */
    public function isNotNull(): bool;

    /**
     * Check whether column have a numeric type
     *
     * @return bool
     */
    public function isNumeric(): bool;

    /**
     * Column is part of the primary key?
     *
     * @return bool
     */
    public function isPrimary(): bool;

    /**
     * Returns true if number column is unsigned
     *
     * @return bool
     */
    public function isUnsigned(): bool;
}
