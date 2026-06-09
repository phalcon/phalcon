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

class Polygon extends AbstractGeometry
{
    /**
     * @var Point[][]
     */
    protected array $rings;

    public function __construct(array $rings, int $srid = 0)
    {
        $this->rings = $rings;
        $this->srid  = $srid;
    }

    public function getRings(): array
    {
        return $this->rings;
    }

    public function getType(): int
    {
        return Column::TYPE_POLYGON;
    }

    public function ringsWkt(): string
    {
        $parts = [];

        foreach ($this->rings as $ring) {
            $ringParts = [];

            foreach ($ring as $point) {
                $ringParts[] = $point->coordsWkt();
            }

            $parts[] = "(" . implode(", ", $ringParts) . ")";
        }

        return implode(", ", $parts);
    }

    public function toWkt(): string
    {
        return "POLYGON(" . $this->ringsWkt() . ")";
    }
}
