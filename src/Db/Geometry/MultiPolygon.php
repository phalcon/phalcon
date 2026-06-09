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

class MultiPolygon extends AbstractGeometry
{
    /**
     * @var Polygon[]
     */
    protected array $polygons;

    public function __construct(array $polygons, int $srid = 0)
    {
        $this->polygons = $polygons;
        $this->srid     = $srid;
    }

    public function getPolygons(): array
    {
        return $this->polygons;
    }

    public function getType(): int
    {
        return Column::TYPE_MULTIPOLYGON;
    }

    public function toWkt(): string
    {
        $parts = [];

        foreach ($this->polygons as $polygon) {
            $parts[] = "(" . $polygon->ringsWkt() . ")";
        }

        return "MULTIPOLYGON(" . implode(", ", $parts) . ")";
    }
}
