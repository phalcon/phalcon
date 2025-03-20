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

namespace Phalcon\Annotations\Models\MetaData;

use Attribute;

#[Attribute(Attribute::TARGET_PROPERTY)]
class Column
{
    public function __construct(
        public string | null $column = null,
        public string $type = 'string',
        public int | null $length = null,
        public bool $nullable = false,
        public bool $skipOnInsert = false,
        public bool $skipOnUpdate = false,
        public bool $allowEmptyString = false,
        public mixed $default = null,
    ) {
    }
}
