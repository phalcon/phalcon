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

use Atlas\Statement\Statement;
use PDO;

use function is_bool;
use function is_int;
use function is_null;

class Value
{
    /**
     * @var int
     */
    protected int $type;

    public function __construct(
        protected mixed $value,
        ?int $type
    ) {
        $this->setType($type);
    }

    /**
     * @return mixed
     */
    public function getValue() : mixed
    {
        return $this->value;
    }

    /**
     * @return int
     */
    public function getType() : int
    {
        return $this->type;
    }

    /**
     * @param int|null $type
     *
     * @return void
     */
    protected function setType(?int $type) : void
    {
        if ($type !== null) {
            $this->type = $type;
        } else {
            $this->type = match (true) {
                is_null($this->value) => PDO::PARAM_NULL,
                is_bool($this->value) => PDO::PARAM_BOOL,
                is_int($this->value)  => PDO::PARAM_INT,
                default               => PDO::PARAM_STR,
            };
        }
    }
}
