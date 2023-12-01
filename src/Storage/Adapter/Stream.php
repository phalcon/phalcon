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

namespace Phalcon\Storage\Adapter;

use DateInterval;
use Exception as BaseException;
use FilesystemIterator;
use Iterator;
use Phalcon\Storage\Exception as StorageException;
use Phalcon\Storage\SerializerFactory;
use Phalcon\Support\Exception as SupportException;
use Phalcon\Traits\Helper\Str\DirFromFileTrait;
use Phalcon\Traits\Helper\Str\DirSeparatorTrait;
use Phalcon\Traits\Php\FileTrait;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

use function fclose;
use function flock;
use function is_dir;
use function is_int;
use function mkdir;
use function restore_error_handler;
use function serialize;
use function set_error_handler;
use function str_replace;
use function time;
use function unlink;

use const E_NOTICE;
use const LOCK_EX;
use const LOCK_SH;

/**
 * Stream adapter
 *
 * @property string $storageDir
 * @property array  $options
 */
class Stream extends AbstractAdapter
{
    use DirFromFileTrait;
    use DirSeparatorTrait;
    use FileTrait;

    /**
     * @var string
     */
    protected string $prefix = 'ph-strm';

    /**
     * @var string
     */
    protected string $storageDir = '';

    /**
     * Stream constructor.
     *
     * @param SerializerFactory $factory
     * @param array             $options = [
     *                                   'storageDir'        => '',
     *                                   'defaultSerializer' => 'php',
     *                                   'lifetime'          => 3600,
     *                                   'prefix'            => ''
     *                                   ]
     *
     * @throws StorageException
     */
    public function __construct(
        SerializerFactory $factory,
        array $options = []
    ) {
        $storageDir = $options['storageDir'] ?? '';
        if (empty($storageDir)) {
            throw new StorageException(
                "The 'storageDir' must be specified in the options"
            );
        }

        /**
         * Lets set some defaults and options here
         */
        $this->storageDir = $this->toDirSeparator($storageDir);

        parent::__construct($factory, $options);

        $this->initSerializer();
    }

    /**
     * Flushes/clears the cache
     */
    public function clear(): bool
    {
        $result   = true;
        $iterator = $this->getIterator($this->storageDir);

        foreach ($iterator as $file) {
            if (
                true === $file->isFile() &&
                true !== $this->phpUnlink($file->getPathName())
            ) {
                $result = false;
            }
        }

        return $result;
    }

    /**
     * Decrements a stored number
     *
     * @param string $key
     * @param int    $value
     *
     * @return bool|int
     * @throws BaseException
     */
    public function decrement(string $key, int $value = 1)
    {
        if (true !== $this->has($key)) {
            return false;
        }

        $data = $this->get($key);
        $data = (int)$data - $value;

        $result = $this->set($key, $data);
        if (false !== $result) {
            $result = $data;
        }

        return $result;
    }

    /**
     * Reads data from the adapter
     *
     * @param string $key
     *
     * @return bool
     */
    public function delete(string $key): bool
    {
        if (true !== $this->has($key)) {
            return false;
        }

        $filepath = $this->getFilepath($key);

        return unlink($filepath);
    }

    /**
     * Reads data from the adapter
     *
     * @param string     $key
     * @param mixed|null $defaultValue
     *
     * @return mixed|null
     */
    public function get(string $key, $defaultValue = null)
    {
        $filepath = $this->getFilepath($key);

        if (true !== $this->phpFileExists($filepath)) {
            return $defaultValue;
        }

        $payload = $this->getPayload($filepath);

        if (empty($payload) || $this->isExpired($payload)) {
            return $defaultValue;
        }

        $content = $payload['content'] ?? null;

        return $this->getUnserializedData($content, $defaultValue);
    }


    /**
     * Stores data in the adapter
     *
     * @param string $prefix
     *
     * @return array
     */
    public function getKeys(string $prefix = ''): array
    {
        $files     = [];
        $directory = $this->getDir();

        if (true !== $this->phpFileExists($directory)) {
            return [];
        }

        $iterator = $this->getIterator($directory);

        foreach ($iterator as $file) {
            if (true === $file->isFile()) {
                $files[] = $this->prefix . $file->getFilename();
            }
        }

        return $this->getFilteredKeys($files, $prefix);
    }

    /**
     * Checks if an element exists in the cache and is not expired
     *
     * @param string $key
     *
     * @return bool
     */
    public function has(string $key): bool
    {
        $filepath = $this->getFilepath($key);

        if (true !== $this->phpFileExists($filepath)) {
            return false;
        }

        $payload = $this->getPayload($filepath);
        if (true === empty($payload)) {
            return false;
        }

        return true !== $this->isExpired($payload);
    }

    /**
     * Increments a stored number
     *
     * @param string $key
     * @param int    $value
     *
     * @return bool|int
     * @throws BaseException
     */
    public function increment(string $key, int $value = 1)
    {
        if (true !== $this->has($key)) {
            return false;
        }

        $data = $this->get($key);
        $data = (int)$data + $value;

        $result = $this->set($key, $data);
        if (false !== $result) {
            $result = $data;
        }

        return $result;
    }

    /**
     * Stores data in the adapter. If the TTL is `null` (default) or not defined
     * then the default TTL will be used, as set in this adapter. If the TTL
     * is `0` or a negative number, a `delete()` will be issued, since this
     * item has expired. If you need to set this key forever, you should use
     * the `setForever()` method.
     *
     * @param string                $key
     * @param mixed                 $value
     * @param DateInterval|int|null $ttl
     *
     * @return bool
     * @throws BaseException
     */
    public function set(string $key, $value, $ttl = null): bool
    {
        if (true === is_int($ttl) && $ttl < 1) {
            return $this->delete($key);
        }

        $payload = [
            'created' => time(),
            'ttl'     => $this->getTtl($ttl),
            'content' => $this->getSerializedData($value),
        ];

        return $this->storePayload($payload, $key);
    }

    /**
     * Stores data in the adapter forever. The key needs to manually deleted
     * from the adapter.
     *
     * @param string $key
     * @param mixed  $data
     *
     * @return bool
     */
    public function setForever(string $key, mixed $data): bool
    {
        $payload = [
            'created' => time(),
            'ttl'     => 'forever',
            'content' => $this->getSerializedData($data),
        ];

        return $this->storePayload($payload, $key);
    }

    /**
     * Returns the folder based on the storageDir and the prefix
     *
     * @param string $key
     *
     * @return string
     */
    private function getDir(string $key = ''): string
    {
        $dirPrefix   = $this->toDirSeparator(
            $this->storageDir . $this->prefix
        );
        $dirFromFile = $this->toDirFromFile(
            str_replace($this->prefix, '', $key)
        );

        return $this->toDirSeparator($dirPrefix . $dirFromFile);
    }

    /**
     * Returns the full path to the file
     *
     * @param string $key
     *
     * @return string
     */
    private function getFilepath(string $key): string
    {
        return $this->getDir($key) . str_replace($this->prefix, '', $key);
    }

    /**
     * Returns an iterator for the directory contents
     *
     * @param string $dir
     *
     * @return Iterator
     */
    private function getIterator(string $dir): Iterator
    {
        return new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator(
                $dir,
                FilesystemIterator::SKIP_DOTS
            ),
            RecursiveIteratorIterator::CHILD_FIRST
        );
    }

    /**
     * Gets the file contents and returns an array or an error if something
     * went wrong
     *
     * @param string $filepath
     *
     * @return array
     */
    private function getPayload(string $filepath): array
    {
        $payload = false;
        $pointer = $this->phpFopen($filepath, 'r');

        /**
         * Cannot open file
         */
        if (false === $pointer) {
            return [];
        }

        if (true === flock($pointer, LOCK_SH)) {
            $payload = $this->phpFileGetContents($filepath);
        }

        fclose($pointer);

        /**
         * No results
         */
        if (false === $payload) {
            return [];
        }

        $warning = false;
        set_error_handler(
            function () use (&$warning) {
                $warning = true;
            },
            E_NOTICE
        );

        $data = unserialize($payload);

        restore_error_handler();

        if (true === $warning) {
            $data = [];
        }

        return $data;
    }

    /**
     * Returns if the cache has expired for this item or not
     *
     * @param array $payload
     *
     * @return bool
     */
    private function isExpired(array $payload): bool
    {
        $created = $payload['created'] ?? 0;
        $ttl     = $payload['ttl'] ?? 3600;

        if ('forever' === $ttl) {
            return false;
        }

        return ((int)$created + (int)$ttl) < time();
    }

    /**
     * Stores an array payload on the file system
     *
     * @param array  $payload
     * @param string $key
     *
     * @return bool
     */
    private function storePayload(array $payload, string $key): bool
    {
        $payload   = serialize($payload);
        $directory = $this->getDir($key);

        if (true !== is_dir($directory)) {
            mkdir($directory, 0777, true);
        }

        return (
            false !== $this->phpFilePutContents($directory . $key, $payload, LOCK_EX)
        );
    }
}
