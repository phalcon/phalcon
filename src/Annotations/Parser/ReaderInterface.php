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

namespace Phalcon\Annotations\Parser;

/**
 * Parses attributes returning an array with the found attributes
 */
interface ReaderInterface
{
    /**
     * Reads attributes from the class, properties and methods
     */
    public function parse(string $className): array;
}
