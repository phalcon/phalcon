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

namespace Phalcon\Annotations;

/**
 * Parses docblocks returning an array with the found annotations
 */
interface ReaderInterface
{
    /**
     * Reads annotations from the class docblocks, its constants, properties
     * and methods
     */
    public function parse(string $className): array;

    /**
     * Parses a raw docblock returning the annotations found
     *
     * @param string     $docBlock
     * @param mixed|null $file
     * @param mixed|null $line
     *
     * @return array
     */
    public static function parseDocBlock(
        string $docBlock,
        mixed $file = null,
        mixed $line = null
    ): array;
}
