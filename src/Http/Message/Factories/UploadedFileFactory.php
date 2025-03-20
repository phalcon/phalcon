<?php

/**
 * This file is part of the Phalcon Framework.
 *
 * (c) Phalcon Team <team@phalcon.io>
 *
 * For the full copyright and license information, please view the LICENSE.txt
 * file that was distributed with this source code.
 *
 * Implementation of this file has been influenced by Nyholm/psr7 and Laminas
 *
 * @link    https://github.com/Nyholm/psr7
 * @license https://github.com/Nyholm/psr7/blob/master/LICENSE
 * @link    https://github.com/laminas/laminas-diactoros
 * @license https://github.com/zendframework/zend-diactoros/blob/master/LICENSE.md
 */

namespace Phalcon\Http\Message\Factories;

use Phalcon\Http\Message\Exception\InvalidArgumentException;
use Phalcon\Http\Message\Interfaces\StreamInterface;
use Phalcon\Http\Message\Interfaces\UploadedFileFactoryInterface;
use Phalcon\Http\Message\Interfaces\UploadedFileInterface;
use Phalcon\Http\Message\UploadedFile;

/**
 * Factory for UploadedFile objects
 */
final class UploadedFileFactory implements UploadedFileFactoryInterface
{
    /**
     * Create a new uploaded file.
     *
     * If a size is not provided it will be determined by checking the size of
     * the stream.
     *
     * @link httsp://php.net/manual/features.file-upload.post-method.php
     * @link https://php.net/manual/features.file-upload.errors.php
     *
     * @param StreamInterface $stream          The underlying stream representing the uploaded file content
     * @param int|null        $size            The size of the file in bytes
     * @param int             $error           The PHP file upload error
     * @param string|null     $clientFilename  The filename as provided by the client, if any
     * @param string|null     $clientMediaType The media type as provided by the client, if any
     *
     * @throws InvalidArgumentException If the file resource is not readable.
     * @return UploadedFileInterface
     */
    public function createUploadedFile(
        StreamInterface $stream,
        int | null $size = null,
        int $error = 0,
        string | null $clientFilename = null,
        string | null $clientMediaType = null
    ): UploadedFileInterface {
        return new UploadedFile(
            $stream,
            $size,
            $error,
            $clientFilename,
            $clientMediaType
        );
    }
}
