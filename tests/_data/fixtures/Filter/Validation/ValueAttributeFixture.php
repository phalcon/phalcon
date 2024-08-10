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

namespace Phalcon\Tests\Fixtures\Filter\Validation;

class ValueAttributeFixture
{
    /**
     * @param mixed|null $name
     */
    public function __construct(
        private mixed $name = null
    ) {
    }

    /**
     * @return mixed
     */
    public function readAttribute(): mixed
    {
        return $this->name;
    }

    /**
     * @param mixed $value
     *
     * @return void
     */
    public function writeAttribute(string $field, mixed $value): void
    {
        $this->$field = $value;
    }
}
