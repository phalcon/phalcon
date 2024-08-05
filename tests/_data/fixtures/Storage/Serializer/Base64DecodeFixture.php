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

use Phalcon\Storage\Serializer\Base64;

class Base64DecodeFixture extends Base64
{
    /**
     * Wrapper for base64_decode
     *
     * @param string $string
     * @param bool   $strict
     *
     * @return string|false
     */
    protected function phpBase64Decode(string $string, bool $strict = false)
    {
        return false;
    }
}
