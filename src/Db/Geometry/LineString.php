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

class LineString extends AbstractGeometry
{
    /**
     * @var Point[]
     */
    protected array $points;

    public function __construct(array $points, int $srid = 0)
    {
        $this->points = $points;
        $this->srid   = $srid;
    }

    public function getPoints(): array
    {
        return $this->points;
    }

    public function getType(): int
    {
        return Column::TYPE_LINESTRING;
    }

    public function pointsWkt(): string
    {
        $parts = [];

        foreach ($this->points as $point) {
            $parts[] = $point->coordsWkt();
        }

        return implode(", ", $parts);
    }

    public function toWkt(): string
    {
        return "LINESTRING(" . $this->pointsWkt() . ")";
    }
}
