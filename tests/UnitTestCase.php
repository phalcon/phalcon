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

namespace Phalcon\Tests;

use Exception;
use Memcached;
use PHPUnit\Framework\SkippedTestSuiteError;
use PHPUnit\Framework\TestCase;
use Predis\Client as RedisDriver;
use ReflectionClass;
use ReflectionException;

use function array_slice;
use function array_unshift;
use function call_user_func_array;
use function extension_loaded;
use function file_exists;
use function func_get_args;
use function gc_collect_cycles;
use function getOptionsLibmemcached;
use function getOptionsRedis;
use function glob;
use function is_dir;
use function is_file;
use function is_object;
use function rmdir;
use function rtrim;
use function sprintf;
use function substr;
use function uniqid;
use function unlink;

use const DIRECTORY_SEPARATOR;
use const GLOB_MARK;

class UnitTestCase extends TestCase
{
    /**
     * @param string $fileName
     * @param string $stream
     *
     * @return void
     */
    public function assertFileContentsContains(string $fileName, string $stream): void
    {
        $contents = file_get_contents($fileName);
        $this->assertStringContainsString($stream, $contents);
    }

    /**
     * @param string $fileName
     * @param string $stream
     *
     * @return void
     */
    public function assertFileContentsEqual(string $fileName, string $stream): void
    {
        $contents = file_get_contents($fileName);
        $this->assertEquals($contents, $stream);
    }

    /**
     * Calls private or protected method.
     *
     * @param string|object $obj
     * @param string        $method
     *
     * @return mixed
     * @throws ReflectionException
     */
    public function callProtectedMethod(
        string | object $obj,
        string $method
    ): mixed {
        $reflectionClass  = new ReflectionClass($obj);
        $reflectionMethod = $reflectionClass->getMethod($method);
        $reflectionMethod->setAccessible(true);

        if (!is_object($obj)) {
            $obj = $reflectionClass->newInstanceWithoutConstructor();
        }

        // $obj, $method
        $args = array_slice(func_get_args(), 2);

        array_unshift($args, $obj);

        return call_user_func_array(
            [$reflectionMethod, 'invoke'],
            $args
        );
    }

    /**
     * Checks if an extension is loaded and if not, skips the test
     *
     * @param string $extension The extension to check
     *
     * @return void
     */
    public function checkExtensionIsLoaded(string $extension): void
    {
        if (true !== extension_loaded($extension)) {
            throw new SkippedTestSuiteError(
                sprintf("Extension '%s' is not loaded. Skipping test", $extension)
            );
        }
    }

    public function clearMemcached(): void
    {
        $adapter = $this->getMemcached();

        $adapter->flush();
    }

    /**
     * Checks whether a key exists
     *
     * @param string $key The key name
     */
    public function doesNotHaveMemcachedKey(string $key): bool
    {
        return false === $this->hasMemcachedKey($key);
    }

    /**
     * Checks whether a key exists
     *
     * @param string $key The key name
     */
    public function doesNotHaveRedisKey(string $key): bool
    {
        return false === $this->hasRedisKey($key);
    }

    /**
     * Returns a directory string with the trailing directory separator
     *
     * @param string $directory
     *
     * @return string
     */
    public function getDirSeparator(string $directory): string
    {
        return rtrim($directory, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
    }

    /**
     * Returns the value of a given key
     *
     * @param string     $key
     * @param mixed|null $value
     *
     * @return void
     */
    public function getMemcachedKey(string $key): mixed
    {
        $adapter = $this->getMemcached();

        return $adapter->get($key);
    }

    /**
     * Returns a unique file name
     *
     * @param string $prefix A prefix for the file
     * @param string $suffix A suffix for the file
     *
     * @return string
     */
    public function getNewFileName(string $prefix = '', string $suffix = 'log')
    {
        $prefix = ($prefix) ? $prefix . '_' : '';
        $suffix = ($suffix) ?: 'log';

        return uniqid($prefix, true) . '.' . $suffix;
    }

    /**
     * Returns the value of a protected property
     *
     * @param object|string $obj
     * @param string        $property
     *
     * @return mixed
     * @throws ReflectionException
     */
    public function getProtectedProperty(
        object | string $obj,
        string $property
    ): mixed {
        $reflection = new ReflectionClass($obj);

        $property = $reflection->getProperty($property);
        $property->setAccessible(true);

        return $property->getValue($obj);
    }

    /**
     * Returns the value of a given key
     *
     * @param string $key The key name
     *
     * @throws Exception if the key does not exist
     */
    public function getRedisKey(string $key): array | string | null
    {
        $adapter = $this->getRedis();

        return $adapter->get($key);
    }

    /**
     * Checks whether a key exists
     *
     * @param string $key The key name
     */
    public function hasMemcachedKey(string $key): bool
    {
        $adapter = $this->getMemcached();

        return null !== $adapter->get($key);
    }

    /**
     * Checks whether a key exists
     *
     * @param string $key The key name
     */
    public function hasRedisKey(string $key): bool
    {
        $adapter = $this->getRedis();

        return (bool)$adapter->exists($key);
    }

    /**
     * Deletes a directory recursively
     *
     * @param string $directory
     */
    public function safeDeleteDirectory(string $directory)
    {
        $files = glob($directory . '*', GLOB_MARK);
        foreach ($files as $file) {
            if (substr($file, -1) == '/') {
                $this->safeDeleteDirectory($file);
            } else {
                unlink($file);
            }
        }

        if (is_dir($directory)) {
            rmdir($directory);
        }
    }

    /**
     * Deletes a file if it exists
     *
     * @param string $filename
     */
    public function safeDeleteFile(string $filename)
    {
        if (file_exists($filename) && is_file($filename)) {
            gc_collect_cycles();
            unlink($filename);
        }
    }

    /**
     * Sends a command directly to the Redis driver. See documentation at
     * https://github.com/nrk/predis
     * Every argument that follows the $command name will be passed to it.
     *
     * @param string $command The command name
     *
     * @return mixed
     */
    public function sendRedisCommand(string $command): mixed
    {
        return call_user_func_array(
            [$this->getRedis(), $command],
            array_slice(func_get_args(), 1)
        );
    }

    /**
     * Creates or modifies keys
     *
     * @param string $key
     * @param mixed  $value
     * @param int    $expiration
     *
     * @return void
     */
    public function setMemcachedKey(
        string $key,
        mixed $value,
        int $expiration = 0
    ): void {
        $adapter = $this->getMemcached();

        $this->assertTrue($adapter->set($key, $value, $expiration));
    }

    /**
     * Sets a protected property
     *
     * @param object|string $obj
     * @param string        $property
     * @param mixed         $value
     *
     * @return void
     * @throws ReflectionException
     */
    public function setProtectedProperty(
        object | string $obj,
        string $property,
        mixed $value
    ): void {
        $reflection = new ReflectionClass($obj);

        $property = $reflection->getProperty($property);

        $property->setAccessible(true);
        $property->setValue($obj, $value);
    }

    /**
     * Creates or modifies keys
     *
     * @param string $key   The key name
     * @param mixed  $value The value
     *
     * @throws Exception
     */
    public function setRedisKey(
        string $key,
        mixed $value
    ): void {
        $adapter = $this->getRedis();

        $result = $adapter->set($key, $value);

        if (false === $result) {
            throw new Exception('Cannot set key in Redis');
        }
    }

    /**
     * @return Memcached
     */
    private function getMemcached(): Memcached
    {
        $options = getOptionsLibmemcached();
        $server  = $options['servers'][0];
        $adapter = new Memcached();
        $adapter->addServer($server['host'], (int) $server['port']);

        return $adapter;
    }

    /**
     * @return RedisDriver
     */
    private function getRedis(): RedisDriver
    {
        try {
            $adapter = new RedisDriver(getOptionsRedis());

            return $adapter;
        } catch (Exception $exception) {
            $this->markTestSkipped(
                __CLASS__ . ' - ' . $exception->getMessage()
            );
        }
    }
}
