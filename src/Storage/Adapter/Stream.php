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

use FilesystemIterator;
use Iterator;
use Phalcon\Contracts\Storage\StorageTypes;
use Phalcon\Storage\Exceptions\InvalidConfiguration;
use Phalcon\Storage\SerializerFactory;
use Phalcon\Support\Traits\FilePathTrait;
use Phalcon\Traits\Php\FileTrait;
use Phalcon\Traits\Support\Helper\Str\DirFromFileTrait;
use Phalcon\Traits\Support\Helper\Str\DirSeparatorTrait;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use SplFileInfo;

use function error_clear_last;
use function error_reporting;
use function fclose;
use function flock;
use function is_int;
use function restore_error_handler;
use function serialize;
use function set_error_handler;
use function str_replace;
use function time;

use const E_NOTICE;
use const LOCK_EX;
use const LOCK_SH;

/**
 * Stream adapter
 *
 * Capabilities:
 * - Counters: read-modify-write (doHas()/doGet()/doSet()); not atomic and racy
 *   across concurrent processes.
 * - getKeys(): recursive directory traversal; cost grows with the entry count.
 * - Serializers: Phalcon-side only.
 *
 * @phpstan-import-type storage_keys from StorageTypes
 * @phpstan-import-type storage_stream_options from StorageTypes
 * @phpstan-import-type storage_stream_payload from StorageTypes
 */
class Stream extends AbstractAdapter
{
    use DirFromFileTrait;
    use DirSeparatorTrait;
    use FilePathTrait;
    use FileTrait;

    protected string $prefix     = 'ph-strm';
    protected string $storageDir = '';

    /**
     * Stream constructor.
     *
     * @phpstan-param storage_stream_options $options
     *
     * @throws InvalidConfiguration
     */
    public function __construct(
        SerializerFactory $factory,
        array $options = []
    ) {
        /** @var string $storageDir */
        $storageDir = $this->getArrVal($options, 'storageDir', '');
        if (empty($storageDir)) {
            throw new InvalidConfiguration(
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
        $result    = true;
        $directory = $this->getDir();

        if (true !== $this->phpFileExists($directory)) {
            return $result;
        }

        $iterator = $this->getIterator($directory);

        /** @var SplFileInfo $file */
        foreach ($iterator as $file) {
            if (true === $file->isFile() && true !== $this->phpUnlink($file->getPathName())) {
                $result = false;
            }
        }

        return $result;
    }

    /**
     * Stores data in the adapter
     *
     * @phpstan-return storage_keys
     */
    public function getKeys(string $prefix = ''): array
    {
        $files     = [];
        $directory = $this->getDir();

        if (true !== $this->phpFileExists($directory)) {
            return [];
        }

        $iterator = $this->getIterator($directory);

        /** @var SplFileInfo $file */
        foreach ($iterator as $file) {
            if (true === $file->isFile()) {
                $files[] = $this->prefix . $file->getFilename();
            }
        }

        return $this->getFilteredKeys($files, $prefix);
    }

    /**
     * Stores data in the adapter forever. The key needs to manually deleted
     * from the adapter.
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
     * Decrements a stored number
     */
    protected function doDecrement(string $key, int $value = 1): false | int
    {
        if (true !== $this->doHas($key)) {
            return false;
        }

        /** @var float|int|string $data */
        $data = $this->doGet($key);
        $data = (int)$data - $value;

        $result = $this->doSet($key, $data);
        if (false !== $result) {
            $result = $data;
        }

        return $result;
    }

    /**
     * Deletes data from the adapter
     */
    protected function doDelete(string $key): bool
    {
        if (true !== $this->doHas($key)) {
            return false;
        }

        $filepath = $this->getFilepath($key);

        return $this->phpUnlink($filepath);
    }

    /**
     * Reads data from the adapter
     */
    protected function doGet(string $key, mixed $defaultValue = null): mixed
    {
        $filepath = $this->getFilepath($key);

        if (true !== $this->phpFileExists($filepath)) {
            return $defaultValue;
        }

        $payload = $this->getPayload($filepath);

        if (empty($payload) || $this->isExpired($payload)) {
            return $defaultValue;
        }

        $content = $this->getArrVal($payload, 'content');

        return $this->getUnserializedData($content, $defaultValue);
    }

    /**
     * Checks if an element exists in the cache and is not expired
     */
    protected function doHas(string $key): bool
    {
        $filepath = $this->getFilepath($key);

        if (true !== $this->phpFileExists($filepath)) {
            return false;
        }

        $payload = $this->getPayload($filepath);
        if (empty($payload)) {
            return false;
        }

        return true !== $this->isExpired($payload);
    }

    /**
     * Increments a stored number
     */
    protected function doIncrement(string $key, int $value = 1): false | int
    {
        if (true !== $this->doHas($key)) {
            return false;
        }

        /** @var float|int|string $data */
        $data = $this->doGet($key);
        $data = (int)$data + $value;

        $result = $this->doSet($key, $data);
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
     */
    protected function doSet(string $key, mixed $value, mixed $ttl = null): bool
    {
        if (is_int($ttl) && $ttl < 1) {
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
     * Returns the folder based on the storageDir and the prefix
     */
    private function getDir(string $key = ''): string
    {
        $dirPrefix   = $this->toDirSeparator(
            $this->storageDir . $this->prefix
        );
        $dirFromFile = $this->toDirFromFile($this->getKeyWithoutPrefix($key));

        return $this->toDirSeparator($dirPrefix . $dirFromFile);
    }

    /**
     * Returns the full path to the file
     */
    private function getFilepath(string $key): string
    {
        /**
         * Remove path separators from the key so a crafted key cannot climb
         * out of the storage directory (CWE-22). str_replace is used rather
         * than prepareVirtualPath because the latter also lower-cases the key,
         * which would no longer match the stored file name.
         */
        $plain = $this->getKeyWithoutPrefix($key);
        $name  = str_replace(['/', '\\', ':'], '_', $plain);

        /**
         * A key with a path separator gets a hash suffix, so it cannot share
         * a file with a key that spells the "_" replacement itself.
         */
        if (str_contains($plain, '/') || str_contains($plain, '\\') || str_contains($plain, ':')) {
            $name .= '_' . sha1($plain);
        }

        return $this->getDir($key) . $name;
    }

    /**
     * Returns an iterator for the directory contents
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
     * @phpstan-return storage_stream_payload
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
            function () use (&$warning): bool {
                $warning = true;

                return true;
            },
            E_NOTICE
        );

        try {
            /**
             * The payload is only ever a metadata array (the stored value is a
             * nested serialized string). Refuse to build any object so a
             * crafted cache file cannot fire magic methods on read (CWE-502).
             *
             * @var false|storage_stream_payload $data
             */
            $data = unserialize($payload, ['allowed_classes' => false]);
        } catch (\ValueError $e) {
            $data = [];
        }

        restore_error_handler();

        if (true === $warning || !is_array($data)) {
            $data = [];
        }

        return $data;
    }

    /**
     * Returns if the cache has expired for this item or not
     *
     * @phpstan-param storage_stream_payload $payload
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
     * @phpstan-param storage_stream_payload $payload
     */
    private function storePayload(array $payload, string $key): bool
    {
        $payload   = serialize($payload);
        $directory = $this->getDir($key);

        /**
         * A different process can make the directory after the test. Do not
         * report the "File exists" warning that this condition causes.
         */
        if (true !== $this->phpIsDir($directory)) {
            $errorLevel = error_reporting(0);
            $this->phpMkdir($directory, 0755, true);
            error_clear_last();
            error_reporting($errorLevel);
        }

        return false !== $this->phpFilePutContents(
            $this->getFilepath($key),
            $payload,
            LOCK_EX
        );
    }
}
