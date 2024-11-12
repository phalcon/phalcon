<?php

/**
 *
 * This file is part of Atlas for PHP.
 *
 * @license http://opensource.org/licenses/mit-license.php MIT
 *
 */

declare(strict_types=1);

namespace Phalcon\DataMapper\Statement;

use function array_merge;

class Delete extends AbstractConditions
{
    /**
     * Delete constructor.
     *
     * @param string $driver
     */
    public function __construct(string $driver)
    {
        parent::__construct($driver);

        $this->store['FROM']      = '';
        $this->store['RETURNING'] = [];
    }


    /**
     * Add table(s) in the query
     *
     * @param string $table
     *
     * @return static
     */
    public function table(string $table): static
    {
        $this->store['FROM'] = $table;

        return $this;
    }

    /**
     * Returns the generated statement
     *
     * @return string
     */
    public function getStatement(): string
    {
        return $this->buildWith()
            . 'DELETE'
            . $this->buildFlags()
            . ' FROM ' . $this->store['FROM']
            . $this->buildCondition('WHERE')
            . $this->buildLimit()
            . $this->buildReturning();
    }

    /**
     * Resets the internal store
     *
     * @return void
     */
    public function reset(): void
    {
        parent::reset();

        $this->store['FROM']      = '';
        $this->store['RETURNING'] = [];
    }

    /**
     * Resets the `RETURNING` store
     *
     * @return $this
     */
    public function resetReturning(): static
    {
        $this->store['RETURNING'] = [];

        return $this;
    }

    /**
     * Adds the `RETURNING` clause
     *
     * @param array $columns
     *
     * @return $this
     */
    public function returning(array $columns): static
    {
        $this->store['RETURNING'] = array_merge(
            $this->store['RETURNING'],
            $columns
        );

        return $this;
    }
}
