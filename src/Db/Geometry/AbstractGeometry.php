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

namespace Phalcon\Db\Geometry;

abstract class AbstractGeometry implements GeometryInterface
{
    /**
     * @var int
     */
    protected int $srid = 0;

    public function __toString(): string
    {
        return $this->toWkt();
    }

    public function getSrid(): int
    {
        return $this->srid;
    }

    abstract public function getType(): int;

    abstract public function toWkt(): string;
}
