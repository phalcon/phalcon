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

namespace Phalcon\Image;

class Enum
{
    // Resizing constraints
    public const AUTO       = 4;
    public const HEIGHT     = 3;
    public const HORIZONTAL = 11;
    public const INVERSE    = 5;
    public const NONE       = 1;
    public const PRECISE    = 6;
    public const TENSILE    = 7;

    // Flipping directions
    public const VERTICAL = 12;
    public const WIDTH    = 2;
}
