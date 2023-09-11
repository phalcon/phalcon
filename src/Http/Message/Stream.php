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

namespace Phalcon\Http\Message;

use Exception;
use Phalcon\Http\Message\Exception\RuntimeException;
use Phalcon\Http\Message\Interfaces\StreamInterface;
use Phalcon\Traits\Php\FileTrait;

use function fclose;
use function feof;
use function fread;
use function fseek;
use function fstat;
use function ftell;
use function get_resource_type;
use function is_resource;
use function is_string;
use function restore_error_handler;
use function set_error_handler;
use function stream_get_contents;
use function stream_get_meta_data;
use function strpbrk;

use const E_WARNING;

/**
 * Stream/file OO class
 *
 * @property resource|null   $handle
 * @property resource|string $stream
 */
class Stream implements StreamInterface
{
    use FileTrait;

    /**
     * @var resource|null
     */
    protected $handle = null;

    /**
     * @var resource|string
     */
    protected $stream;

    /**
     * Stream constructor.
     *
     * @param mixed  $stream
     * @param string $mode
     */
    public function __construct($stream, string $mode = "rb")
    {
        $this->setStream($stream, $mode);
    }

    /**
     * Closes the stream when the destructed.
     */
    public function __destruct()
    {
        $this->close();
    }

    /**
     * Reads all data from the stream into a string, from the beginning to end.
     *
     * This method MUST attempt to seek to the beginning of the stream before
     * reading data and read the stream until the end is reached.
     *
     * Warning: This could attempt to load a large amount of data into memory.
     *
     * This method MUST NOT raise an exception in order to conform with PHP's
     * string casting operations.
     *
     * @see http://php.net/manual/en/language.oop5.magic.php#object.tostring
     *
     * @return string
     */
    public function __toString(): string
    {
        $result = "";
        try {
            if (true === $this->isReadable()) {
                if (true === $this->isSeekable()) {
                    $this->rewind();
                }

                $result = $this->getContents();
            }
        } catch (Exception $ex) {
            $result = "";
        }

        return $result;
    }

    /**
     * Closes the stream and any underlying resources.
     */
    public function close(): void
    {
        if (null !== $this->handle) {
            $handle = $this->detach();

            fclose($handle);
        }
    }

    /**
     * Separates any underlying resources from the stream.
     *
     * After the stream has been detached, the stream is in an unusable state.
     *
     * @return resource|null
     */
    public function detach()
    {
        $handle       = $this->handle;
        $this->handle = null;

        return $handle;
    }

    /**
     * Returns true if the end of the stream has been reached
     *
     * @return bool
     */
    public function eof(): bool
    {
        if (null !== $this->handle) {
            return feof($this->handle);
        }

        return true;
    }

    /**
     * Returns the remaining contents in a string
     *
     * @return string
     * @throws RuntimeException
     */
    public function getContents(): string
    {
        $this->checkHandle();
        $this->checkReadable();

        $data = stream_get_contents($this->handle);

        if (false === $data) {
            throw new RuntimeException("Could not read from the file/stream");
        }

        return $data;
    }

    /**
     * Get stream metadata as an associative array or retrieve a specific key.
     *
     * The keys returned are identical to the keys returned from PHP's
     * stream_get_meta_data() function.
     *
     * @param mixed|null $key
     *
     * @return array|mixed|null
     */
    public function getMetadata(string $key = null)
    {
        if (null === $this->handle) {
            return null;
        }

        $metadata = stream_get_meta_data($this->handle);

        if (null === $key) {
            return $metadata;
        }

        return $metadata[$key] ?? [];
    }

    /**
     * Get the size of the stream if known.
     *
     * @return int|null
     */
    public function getSize(): ?int
    {
        if (null !== $this->handle) {
            $stats = fstat($this->handle);

            if (false !== $stats) {
                return $stats["size"] ?? null;
            }
        }

        return null;
    }

    /**
     * Returns whether the stream is readable.
     *
     * @return bool
     */
    public function isReadable(): bool
    {
        $mode = (string)$this->getMetadata("mode");

        return false !== strpbrk($mode, "r+");
    }

    /**
     * Returns whether the stream is seekable.
     *
     * @return bool
     */
    public function isSeekable(): bool
    {
        return (bool)$this->getMetadata("seekable");
    }

    /**
     * Returns whether the stream is writable.
     *
     * @return bool
     */
    public function isWritable(): bool
    {
        $mode = (string)$this->getMetadata("mode");

        return false !== strpbrk($mode, "xwca+");
    }

    /**
     * Read data from the stream.
     *
     * @param int $length
     *
     * @return string
     * @throws RuntimeException
     */
    public function read(int $length): string
    {
        $this->checkHandle();
        $this->checkReadable();

        $data = fread($this->handle, $length);

        if (false === $data) {
            throw new RuntimeException("Could not read from the file/stream");
        }

        return $data;
    }

    /**
     * Seek to the beginning of the stream.
     *
     * If the stream is not seekable, this method will raise an exception;
     * otherwise, it will perform a seek(0).
     *
     * @return void
     */
    public function rewind(): void
    {
        $this->seek(0);
    }

    /**
     * Seek to a position in the stream.
     *
     * @param int $offset
     * @param int $whence
     *
     * @return void
     * @throws RuntimeException
     */
    public function seek(int $offset, int $whence = 0): void
    {
        $this->checkHandle();
        $this->checkSeekable();

        $seeker = fseek($this->handle, $offset, $whence);

        if (0 !== $seeker) {
            throw new RuntimeException("Could not seek on the file pointer");
        }
    }

    /**
     * Sets the stream - existing instance
     *
     * @param mixed  $stream
     * @param string $mode
     */
    public function setStream($stream, string $mode = "rb"): void
    {
        $handle  = $stream;
        $warning = false;
        if (true === is_string($stream)) {
            set_error_handler(
                function () use (&$warning) {
                    $warning = true;
                },
                E_WARNING
            );

            $handle = $this->phpFopen($stream, $mode);

            restore_error_handler();
        }

        if (
            true === $warning ||
            true !== is_resource($handle) ||
            "stream" !== get_resource_type($handle)
        ) {
            throw new RuntimeException(
                "The stream provided is not valid (string/resource) or could not be opened."
            );
        }

        $this->handle = $handle;
        $this->stream = $stream;
    }

    /**
     * Returns the current position of the file read/write pointer
     *
     * @return int
     * @throws RuntimeException
     */
    public function tell(): int
    {
        $this->checkHandle();

        $position = ftell($this->handle);

        if (false === $position) {
            throw new RuntimeException(
                "Could not retrieve the pointer position"
            );
        }

        return $position;
    }

    /**
     * Write data to the stream.
     *
     * @param string $data
     *
     * @return int
     * @throws RuntimeException
     */
    public function write(string $data): int
    {
        $this->checkHandle();
        $this->checkWritable();

        $bytes = $this->phpFwrite($this->handle, $data);

        if (false === $bytes) {
            throw new RuntimeException("Could not write to the file/stream");
        }

        return $bytes;
    }

    /**
     * Checks if a handle is available and throws an exception otherwise
     *
     * @return void
     * @throws RuntimeException
     */
    private function checkHandle(): void
    {
        if (null === $this->handle) {
            throw new RuntimeException("A valid resource is required.");
        }
    }

    /**
     * Checks if a handle is readable and throws an exception otherwise
     *
     * @return void
     * @throws RuntimeException
     */
    private function checkReadable(): void
    {
        if (true !== $this->isReadable()) {
            throw new RuntimeException("The resource is not readable.");
        }
    }

    /**
     * Checks if a handle is seekable and throws an exception otherwise
     *
     * @return void
     * @throws RuntimeException
     */
    private function checkSeekable(): void
    {
        if (true !== $this->isSeekable()) {
            throw new RuntimeException("The resource is not seekable.");
        }
    }

    /**
     * Checks if a handle is writeable and throws an exception otherwise
     *
     * @return void
     * @throws RuntimeException
     */
    private function checkWritable(): void
    {
        if (true !== $this->isWritable()) {
            throw new RuntimeException("The resource is not writable.");
        }
    }
}
