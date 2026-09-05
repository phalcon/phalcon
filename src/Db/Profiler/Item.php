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

namespace Phalcon\Db\Profiler;

use Phalcon\Contracts\Db\DbTypes;
use Phalcon\Db\Traits\ElapsedTimeTrait;

/**
 * This class identifies each profile in a Phalcon\Db\Profiler
 *
 * @phpstan-import-type db_bind_params from DbTypes
 * @phpstan-import-type db_bind_types from DbTypes
 */
class Item
{
    use ElapsedTimeTrait;

    /**
     * Timestamp when the profile ended
     */
    protected float $finalTime;

    /**
     * Timestamp when the profile started
     */
    protected float $initialTime;

    /**
     * SQL bind types related to the profile
     *
     * @var db_bind_types
     */
    protected array $sqlBindTypes;

    /**
     * SQL statement related to the profile
     */
    protected string $sqlStatement;

    /**
     * SQL variables related to the profile
     *
     * @var db_bind_params
     */
    protected array $sqlVariables;

    /**
     * Return the timestamp when the profile ended
     */
    public function getFinalTime(): float
    {
        return $this->finalTime;
    }

    /**
     * Return the timestamp when the profile started
     */
    public function getInitialTime(): float
    {
        return $this->initialTime;
    }

    /**
     * Return the SQL bind types related to the profile
     *
     * @phpstan-return db_bind_types
     */
    public function getSqlBindTypes(): array
    {
        return $this->sqlBindTypes;
    }

    /**
     * Return the SQL statement related to the profile
     */
    public function getSqlStatement(): string
    {
        return $this->sqlStatement;
    }

    /**
     * Return the SQL variables related to the profile
     *
     * @phpstan-return db_bind_params
     */
    public function getSqlVariables(): array
    {
        return $this->sqlVariables;
    }

    /**
     * Returns the total time in nanoseconds spent by the profile
     */
    public function getTotalElapsedNanoseconds(): float
    {
        return $this->finalTime - $this->initialTime;
    }

    /**
     * Return the timestamp when the profile ended
     *
     * @return $this
     */
    public function setFinalTime(float $finalTime): static
    {
        $this->finalTime = $finalTime;

        return $this;
    }

    /**
     * Return the timestamp when the profile started
     *
     * @return $this
     */
    public function setInitialTime(float $initialTime): static
    {
        $this->initialTime = $initialTime;

        return $this;
    }

    /**
     * Return the SQL bind types related to the profile
     *
     * @phpstan-param db_bind_types $sqlBindTypes
     *
     * @return $this
     */
    public function setSqlBindTypes(array $sqlBindTypes): static
    {
        $this->sqlBindTypes = $sqlBindTypes;

        return $this;
    }

    /**
     * Return the SQL statement related to the profile
     *
     * @return $this
     */
    public function setSqlStatement(string $sqlStatement): static
    {
        $this->sqlStatement = $sqlStatement;

        return $this;
    }

    /**
     * Return the SQL variables related to the profile
     *
     * @phpstan-param db_bind_params $sqlVariables
     *
     * @return $this
     */
    public function setSqlVariables(array $sqlVariables): static
    {
        $this->sqlVariables = $sqlVariables;

        return $this;
    }
}
