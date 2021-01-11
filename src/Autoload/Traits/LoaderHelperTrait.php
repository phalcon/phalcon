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

namespace Phalcon\Autoload\Traits;

use Phalcon\Autoload\Exception;

use function file_exists;
use function is_array;
use function is_string;
use function rtrim;
use function str_replace;
use function strrpos;
use function substr;

use const DIRECTORY_SEPARATOR;

/**
 * Trait LoaderHelperTrait
 *
 * @package Phalcon\Autoload\Traits
 *
 * @property array $classes
 * @property array $debug
 * @property array $extensions
 * @property array $files
 * @property bool  $isRegistered
 * @property array $namespaces
 */
trait LoaderHelperTrait
{
    /**
     * @var array
     */
    protected array $classes = [];

    /**
     * @var array
     */
    protected array $debug = [];

    /**
     * @var array
     */
    protected array $extensions = [];

    /**
     * @var array
     */
    protected array $files = [];

    /**
     * @var bool
     */
    protected bool $isRegistered = false;

    /**
     * @var array
     */
    protected array $namespaces = [];

    /**
     * If the file exists, require it and return true; false otherwise
     *
     * @param string $file The file to require
     *
     * @return bool
     */
    protected function requireFile(string $file): bool
    {
        if (file_exists($file)) {
            require $file;

            return true;
        }

        return false;
    }

    /**
     * @param mixed $directories
     *
     * @return array
     * @throws Exception
     */
    private function checkDirectories($directories): array
    {
        if (!is_string($directories) && !is_array($directories)) {
            throw new Exception(
                'The directories parameter is not a string or array'
            );
        }

        if (is_string($directories)) {
            $directories = [$directories];
        }

        return $directories;
    }


    /**
     * Search for the file corresponding to the namespaced class and load it
     *
     * @param string $namespace
     * @param string $class
     *
     * @return bool|string
     */
    protected function loadFile(string $namespace, string $class)
    {
        if (!isset($this->namespaces[$namespace])) {
            $this->debug[] = 'Load: No folders registered: ' . $namespace;

            return false;
        }

        return $this->processFileNameSpaces($namespace, $class);
    }

    /**
     * @param array  $directories
     * @param string $dSeparator
     *
     * @return array
     */
    private function processDirectories(array $directories, string $dSeparator): array
    {
        foreach ($directories as $key => $directory) {
            $directories[$key] = rtrim($directory, $dSeparator) . $dSeparator;
        }

        return $directories;
    }

    /**
     * @param string $namespace
     * @param string $class
     *
     * @return bool|string
     */
    private function processFileNameSpaces(string $namespace, string $class)
    {
        $nSeparator = '\\';
        $dSeparator = DIRECTORY_SEPARATOR;

        foreach ($this->namespaces[$namespace] as $directory) {
            foreach ($this->extensions as $extension) {
                $file = $directory
                    . str_replace($nSeparator, $dSeparator, $class)
                    . '.'
                    . $extension;

                if ($this->requireFile($file)) {
                    return $file;
                }

                $this->debug[] = 'Load: 404 : ' . $namespace . ' - ' . $file;
            }
        }

        return false;
    }

    /**
     * @param string $name
     *
     * @return bool|string
     */
    private function processLoadNameSpaces(string $name)
    {
        $nSeparator = '\\';

        /**
         * Namespaces
         *
         * Matching in reverse the namespace names in relation to class names
         */
        $namespace = $name;
        while (false !== $pos = strrpos($namespace, $nSeparator)) {
            // retain the trailing namespace separator in the prefix
            $namespace = substr($name, 0, $pos + 1);
            $remainder = substr($name, $pos + 1);

            $file = $this->loadFile($namespace, $remainder);
            if (false !== $file) {
                $this->debug[] = 'Namespace: ' . $namespace . ' - ' . $file;

                return $file;
            }

            $namespace = rtrim($namespace, $nSeparator);
        }

        // 404
        $this->debug[] = 'Namespace: 404 : ' . $name;

        return false;
    }
}
