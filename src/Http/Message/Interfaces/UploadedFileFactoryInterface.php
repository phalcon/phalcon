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

declare(strict_types=1);

namespace Phalcon\Http\Message\Interfaces;

use Phalcon\Http\Message\Exception\InvalidArgumentException;

use const UPLOAD_ERR_OK;

interface UploadedFileFactoryInterface
{
    /**
     * Create a new uploaded file.
     *
     * If a size is not provided it will be determined by checking the size of
     * the file.
     *
     * @see https://php.net/manual/features.file-upload.post-method.php
     * @see https://php.net/manual/features.file-upload.errors.php
     *
     * @param StreamInterface $stream          Underlying stream representing
     *                                         the uploaded file content.
     * @param int|null        $size            in bytes
     * @param int             $error           PHP file upload error
     * @param string|null     $clientFilename  Filename as provided by the
     *                                         client, if any.
     * @param string|null     $clientMediaType Media type as provided by the
     *                                         client, if any.
     *
     * @return UploadedFileInterface
     *
     * @throws InvalidArgumentException If the file resource is not readable.
     */
    public function createUploadedFile(
        StreamInterface $stream,
        int | null $size = null,
        int $error = UPLOAD_ERR_OK,
        string | null $clientFilename = null,
        string | null $clientMediaType = null
    ): UploadedFileInterface;
}
