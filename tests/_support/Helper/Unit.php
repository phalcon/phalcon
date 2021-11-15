<?php

namespace Helper;

use Codeception\Module;
use PHPUnit\Framework\SkippedTestError;

use ReflectionClass;
use ReflectionException;
use function array_slice;
use function array_unshift;
use function call_user_func_array;
use function extension_loaded;
use function file_exists;
use function func_get_args;
use function glob;
use function is_dir;
use function is_file;
use function is_object;
use function rmdir;
use function sprintf;
use function strtolower;
use function substr;
use function uniqid;
use function unlink;

use const DIRECTORY_SEPARATOR;
use const GLOB_MARK;
use const PHP_EOL;
use const PHP_OS;

// here you can define custom actions
// all public methods declared in helper class will be available in $I

class Unit extends Module
{
    /**
     * Calls private or protected method.
     *
     * @param string|object $obj
     * @param string $method
     *
     * @return mixed
     * @throws ReflectionException
     */
    public function callProtectedMethod($obj, string $method)
    {
        $reflectionClass = new ReflectionClass($obj);

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
     */
    public function checkExtensionIsLoaded(string $extension)
    {
        if (true !== extension_loaded($extension)) {
            $this->skipTest(
                sprintf("Extension '%s' is not loaded. Skipping test", $extension)
            );
        }
    }

    /**
     * Converts line endings and directory separators to the Windows ones
     * if this is a windows build
     *
     * @param string $input
     *
     * @return string
     */
    public function convertDirSeparator(string $input): string
    {
        /**
         * Tests have "\r\n" the typical PHP_EOL and "/" as the
         * DIRECTORY_SEPARATOR. If this is a Windows platform we need to change
         * those
         */
        if (PHP_OS_FAMILY !== 'Windows') {
            $input = str_replace(["\r\n", "/"], ["\n", "\\",], $input);
        }

        return $input;
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
        $suffix = ($suffix) ? $suffix : 'log';

        return uniqid($prefix, true) . '.' . $suffix;
    }

    /**
     * @throws ReflectionException
     */
    public function getProtectedProperty($obj, $prop)
    {
        $reflection = new ReflectionClass($obj);

        $property = $reflection->getProperty($prop);

        $property->setAccessible(true);

        return $property->getValue($obj);
    }

    /**
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
     * @throws ReflectionException
     */
    public function setProtectedProperty($obj, $prop, $value)
    {
        $reflection = new ReflectionClass($obj);

        $property = $reflection->getProperty($prop);

        $property->setAccessible(true);
        $property->setValue($obj, $value);

        $this->assertSame(
            $value,
            $property->getValue($obj)
        );
    }

    /**
     * Throws the SkippedTestError exception to skip a test
     *
     * @param string $message The message to display
     */
    public function skipTest(string $message)
    {
        throw new SkippedTestError($message);
    }
}
