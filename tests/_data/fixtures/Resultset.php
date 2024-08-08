<?php

/**
* This file is part of the Phalcon Framework.
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Phalcon\Tests\Fixtures;

use stdClass;

class Resultset extends stdClass
{
    public $calculated = '';

    public function __construct(string $calculated)
    {
        $this->calculated = $calculated;
    }
}
