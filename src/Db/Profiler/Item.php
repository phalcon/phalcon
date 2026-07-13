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

use Phalcon\Db\Traits\ElapsedTimeTrait;

/**
 * This class identifies each profile in a Phalcon\Db\Profiler
 */
class Item
{
    use ElapsedTimeTrait;

    /**
     * Timestamp when the profile ended
     *
     * @var float
     */
    protected float $finalTime;

    /**
     * Timestamp when the profile started
     *
     * @var float
     */
    protected float $initialTime;

    /**
     * SQL bind types related to the profile
     *
     * @var array
     */
    protected array $sqlBindTypes;

    /**
     * SQL statement related to the profile
     *
     * @var string
     */
    protected string $sqlStatement;

    /**
     * SQL variables related to the profile
     *
     * @var array
     */
    protected array $sqlVariables;

    /**
     * Return the timestamp when the profile ended
     *
     * @return float
     */
    public function getFinalTime(): float
    {
        return $this->finalTime;
    }

    /**
     * Return the timestamp when the profile started
     *
     * @return float
     */
    public function getInitialTime(): float
    {
        return $this->initialTime;
    }

    /**
     * Return the SQL bind types related to the profile
     *
     * @return array
     */
    public function getSqlBindTypes(): array
    {
        return $this->sqlBindTypes;
    }

    /**
     * Return the SQL statement related to the profile
     *
     * @return string
     */
    public function getSqlStatement(): string
    {
        return $this->sqlStatement;
    }

    /**
     * Return the SQL variables related to the profile
     *
     * @return array
     */
    public function getSqlVariables(): array
    {
        return $this->sqlVariables;
    }

    /**
     * Returns the total time in nanoseconds spent by the profile
     *
     * @return float
     */
    public function getTotalElapsedNanoseconds(): float
    {
        return $this->finalTime - $this->initialTime;
    }

    /**
     * Return the timestamp when the profile ended
     *
     * @param float $finalTime
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
     * @param float $initialTime
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
     * @param array $sqlBindTypes
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
     * @param string $sqlStatement
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
     * @param array $sqlVariables
     *
     * @return $this
     */
    public function setSqlVariables(array $sqlVariables): static
    {
        $this->sqlVariables = $sqlVariables;

        return $this;
    }
}
