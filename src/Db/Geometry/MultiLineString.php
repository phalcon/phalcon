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

class MultiLineString extends AbstractGeometry
{
    /**
     * @var LineString[]
     */
    protected array $lineStrings;

    public function __construct(array $lineStrings, int $srid = 0)
    {
        $this->lineStrings = $lineStrings;
        $this->srid        = $srid;
    }

    public function getLineStrings(): array
    {
        return $this->lineStrings;
    }

    public function getType(): int
    {
        return Column::TYPE_MULTILINESTRING;
    }

    public function toWkt(): string
    {
        $parts = [];

        foreach ($this->lineStrings as $line) {
            $parts[] = "(" . $line->pointsWkt() . ")";
        }

        return "MULTILINESTRING(" . implode(", ", $parts) . ")";
    }
}
