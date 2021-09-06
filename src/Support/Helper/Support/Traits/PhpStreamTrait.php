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

namespace Phalcon\Support\Traits;

use function stream_get_contents;
use function stream_get_meta_data;

/**
 * Trait PhpStreamTrait
 *
 * @package Phalcon\Support\Traits
 */
trait PhpStreamTrait
{
    /**
     * @param resource $stream
     * @param int|null $length
     * @param int      $offset
     *
     * @return string|bool
     *
     * @link https://php.net/manual/en/function.stream-get-contents.php
     */
    protected function phpStreamGetContents($stream, ?int $length = -1, int $offset = -1)
    {
        return stream_get_contents($stream, $length, $offset);
    }


    /**
     * Retrieves header/meta data from streams/file pointers
     *
     * @param resource $stream
     *
     * @return array
     *
     * @link https://php.net/manual/en/function.stream-get-meta-data.php
     */
    protected function phpStreamGetMetaData($stream): array
    {
        return stream_get_meta_data($stream);
    }

}
