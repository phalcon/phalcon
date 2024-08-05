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

namespace Phalcon\Tests\Fixtures\Storage\Serializer;

use Phalcon\Storage\Serializer\Igbinary;

class IgbinarySerializeFixture extends Igbinary
{
    /**
     * Wrapper for `igbinary_serialize`
     *
     * @param mixed $value
     *
     * @return string|null
     */
    protected function phpIgbinarySerialize($value): ?string
    {
        return null;
    }
}
