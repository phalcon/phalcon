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

use Phalcon\Contracts\Annotations\AnnotationsTypes;

/**
 * Parses attributes returning an array with the found attributes
 *
 * @phpstan-import-type annotations_reflection_data from AnnotationsTypes
 */
interface ReaderInterface
{
    /**
     * Reads attributes from the class, properties and methods
     *
     * @phpstan-return annotations_reflection_data
     */
    public function parse(string $className): array;
}
