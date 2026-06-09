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

use Phalcon\Db\Column;

use function implode;

class GeometryCollection extends AbstractGeometry
{
    /**
     * @var GeometryInterface[]
     */
    protected array $geometries;

    public function __construct(array $geometries, int $srid = 0)
    {
        $this->geometries = $geometries;
        $this->srid       = $srid;
    }

    public function getGeometries(): array
    {
        return $this->geometries;
    }

    public function getType(): int
    {
        return Column::TYPE_GEOMETRYCOLLECTION;
    }

    public function toWkt(): string
    {
        $parts = [];

        foreach ($this->geometries as $geometry) {
            $parts[] = $geometry->toWkt();
        }

        return "GEOMETRYCOLLECTION(" . implode(", ", $parts) . ")";
    }
}
