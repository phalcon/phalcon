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

namespace Phalcon\Tests\Fixtures\Annotations\Adapter;

use Phalcon\Annotations\Adapter\Stream;

class StreamFileGetContentsFixture extends Stream
{
    /**
     * @param string $filename
     *
     * @return string|false
     *
     * @link https://php.net/manual/en/function.file-get-contents.php
     */
    protected function phpFileGetContents(string $filename)
    {
        return false;
    }
}
