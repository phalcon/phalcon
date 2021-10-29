<?php

/**
 * This file is part of the Phalcon.
 *
 * (c) Phalcon Team <team@phalcon.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Phalcon\Support\Helper\Str;

use Phalcon\Traits\Helper\Str\DirSeparatorTrait;

/**
 * Accepts a directory name and ensures that it ends with
 * DIRECTORY_SEPARATOR
 */
class DirSeparator
{
    use DirSeparatorTrait;

    /**
     * @param string $directory
     *
     * @return string
     */
    public function __invoke(string $directory): string
    {
        return $this->toDirSeparator($directory);
    }
}
