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
use Phalcon\Http\Message\Interfaces\StreamFactoryInterface;
use Phalcon\Http\Message\Interfaces\StreamInterface;
use Phalcon\Http\Message\Stream;
use Phalcon\Traits\Php\FileTrait;

use function get_resource_type;
use function is_resource;
use function rewind;

/**
 * Factory for Stream objects
 */
final class StreamFactory implements StreamFactoryInterface
{
    use FileTrait;

    /**
     * Create a new stream from a string.
     *
     * The stream SHOULD be created with a temporary resource.
     *
     * @param string $content String content with which to populate the stream.
     *
     * @return StreamInterface
     */
    public function createStream(string $content = ""): StreamInterface
    {
        $handle = $this->phpFopen("php://temp", "r+b");
        if (false === $handle) {
            throw new InvalidArgumentException("Cannot write to file");
        }

        $result = $this->phpFwrite($handle, $content);
        if (false === $result) {
            throw new InvalidArgumentException("Write to file process unsuccessful");
        }

        rewind($handle);

        return $this->createStreamFromResource($handle);
    }

    /**
     * Create a stream from an existing file.
     *
     * The file MUST be opened using the given mode, which may be any mode
     * supported by the `fopen` function.
     *
     * The `$filename` MAY be any string supported by `fopen()`.
     *
     * @param string $filename The filename or stream URI to use as basis of
     *                         stream.
     * @param string $mode     The mode with which to open the underlying
     *                         filename/stream.
     *
     * @return StreamInterface
     */
    public function createStreamFromFile(
        string $filename,
        string $mode = "r+b"
    ): StreamInterface {
        return new Stream($filename, $mode);
    }

    /**
     * Create a new stream from an existing resource.
     *
     * The stream MUST be readable and may be writable.
     *
     * @param resource $phpResource
     *
     * @return StreamInterface|void
     */
    public function createStreamFromResource($phpResource): StreamInterface
    {
        if (
            true !== is_resource($phpResource) ||
            "stream" !== get_resource_type($phpResource)
        ) {
            throw new InvalidArgumentException("Invalid stream provided");
        }

        return new Stream($phpResource);
    }
}
