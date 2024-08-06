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

use PHPUnit\Framework\SkippedTestSuiteError;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use ReflectionException;

use function array_slice;
use function array_unshift;
use function call_user_func_array;
use function extension_loaded;
use function file_exists;
use function func_get_args;
use function gc_collect_cycles;
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
}
