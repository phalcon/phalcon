<?php

/**
 * $this file is part of the Phalcon Framework.
 *
 * (c) Phalcon Team <team@phalcon.io>
 *
 * For the full copyright and license information, please view the LICENSE.txt
 * file that was distributed with $this source code.
 *
 * Implementation of $this file has been influenced by AtlasPHP
 *
 * @link    https://github.com/atlasphp/Atlas.Pdo
 * @license https://github.com/atlasphp/Atlas.Pdo/blob/1.x/LICENSE.md
 */

declare(strict_types=1);

namespace Phalcon\DataMapper\Statement;

use Phalcon\DataMapper\Statement\Clause\Traits\Columns\Select as SelectColumns;
use Phalcon\DataMapper\Statement\Clause\Traits\ConditionTrait;
use Phalcon\DataMapper\Statement\Clause\Traits\FlagsTrait;
use Phalcon\DataMapper\Statement\Clause\Traits\FromTrait;
use Phalcon\DataMapper\Statement\Clause\Traits\GroupByTrait;
use Phalcon\DataMapper\Statement\Clause\Traits\HavingTrait;
use Phalcon\DataMapper\Statement\Clause\Traits\LimitTrait;
use Phalcon\DataMapper\Statement\Clause\Traits\OrderByTrait;
use Phalcon\DataMapper\Statement\Clause\Traits\WhereTrait;

use function implode;

class Select extends AbstractStatement
{
    use ConditionTrait;
    use FlagsTrait;
    use FromTrait;
    use GroupByTrait;
    use HavingTrait;
    use LimitTrait;
    use OrderByTrait;
    use SelectColumns;
    use WhereTrait;

    /**
     * @var string
     */
    protected string $asAlias = "";

    /**
     * @var bool
     */
    protected bool $forUpdate = false;

    /**
     * The `AS` statement for the query - useful in sub-queries
     *
     * @param string $asAlias
     *
     * @return Select
     */
    public function asAlias(string $asAlias): static
    {
        $this->asAlias = $asAlias;

        return $this;
    }

    /**
     * @param bool $enable
     *
     * @return Select
     */
    public function distinct(bool $enable = true): static
    {
        $this->setFlag("DISTINCT", $enable);

        return $this;
    }

    /**
     * Enable the `FOR UPDATE` for the query
     *
     * @param bool $enable
     *
     * @return Select
     */
    public function forUpdate(bool $enable = true): static
    {
        $this->forUpdate = $enable;

        return $this;
    }

    /**
     * @return array
     */
    public function getBindValueObjects() : array
    {
        return $this->bind->getValues();
    }

    /**
     * @return array
     */
    public function getBindValueArrays() : array
    {
        $values = [];

        foreach ($this->bind->getValues() as $name => $value) {
            $values[$name] = [$value->getValue(), $value->getType()];
        }

        return $values;
    }

    /**
     * Reset the AS
     *
     * @return $this
     */
    public function resetAs(): static
    {
        $this->asAlias = '';

        return $this;
    }

    /**
     * @return string
     */
    public function getStatement() : string
    {
        return implode('', $this->store['UNION'])
            . $this->getCurrentStatement();
    }
}
