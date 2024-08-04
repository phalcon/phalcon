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

class StreamFilePutContentsFixture extends Stream
{
    /**
     * @param string   $filename
     * @param mixed    $data
     * @param int      $flags
     * @param resource $context
     *
     * @return int|false
     *
     * @link https://php.net/manual/en/function.file-put-contents.php
     */
    protected function phpFilePutContents(
        string $filename,
        $data,
        int $flags = 0,
        $context = null
    ) {
        return false;
    }
}
