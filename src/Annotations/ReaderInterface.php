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

use Phalcon\Contracts\Annotations\AnnotationsTypes;

/**
 * Parses docblocks returning an array with the found annotations
 *
 * @phpstan-import-type annotations_node_list from AnnotationsTypes
 * @phpstan-import-type annotations_reflection_data from AnnotationsTypes
 */
interface ReaderInterface
{
    /**
     * Parses a raw docblock returning the annotations found
     *
     * @phpstan-param string|null $file
     * @phpstan-param int|null    $line
     *
     * @phpstan-return annotations_node_list|false
     */
    public static function parseDocBlock(
        string $docBlock,
        mixed $file = null,
        mixed $line = null
    ): array | false;

    /**
     * Reads annotations from the class docblocks, its constants, properties and methods
     *
     * @phpstan-param class-string $className
     *
     * @phpstan-return annotations_reflection_data
     */
    public function parse(string $className): array;
}
