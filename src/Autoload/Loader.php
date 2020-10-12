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

namespace Phalcon\Autoload;

use Phalcon\Autoload\Traits\LoaderHelperTrait;

use function array_merge;
use function array_unique;
use function array_values;
use function sha1;
use function spl_autoload_register;
use function spl_autoload_unregister;
use function trim;

use const DIRECTORY_SEPARATOR;

/**
 * Class Logger
 *
 * @package Phalcon\Autoload
 */
class Loader
{
    use LoaderHelperTrait;

    /**
     * Loader constructor.
     */
    public function __construct()
    {
        $this->extensions = ['php'];
    }

    /**
     * Adds a class to the internal collection for the mapping
     *
     * @param string $name
     * @param string $file
     *
     * @return Loader
     */
    public function addClass(string $name, string $file): Loader
    {
        $this->classes[$name] = $file;

        return $this;
    }

    /**
     * Adds an extension for the loaded files
     *
     * @param string $extension
     *
     * @return Loader
     */
    public function addExtension(string $extension): Loader
    {
        $this->extensions[] = $extension;
        $this->extensions   = array_unique($this->extensions);

        return $this;
    }

    /**
     * Adds a file to be added to the loader
     *
     * @param string $file
     *
     * @return Loader
     */
    public function addFile(string $file): Loader
    {
        $this->files[sha1($file)] = $file;

        return $this;
    }

    /**
     * @param string $namespace
     * @param mixed  $directories
     * @param bool   $prepend
     *
     * @return Loader
     * @throws Exception
     */
    public function addNamespace(
        string $namespace,
        $directories,
        bool $prepend = false
    ): Loader {
        $nSeparator  = '\\';
        $dSeparator  = DIRECTORY_SEPARATOR;
        $namespace   = trim($namespace, $nSeparator) . $nSeparator;
        $directories = $this->checkDirectories($directories);

        // initialize the namespace prefix array if needed
        if (!isset($this->namespaces[$namespace])) {
            $this->namespaces[$namespace] = [];
        }

        $directories = $this->processDirectories($directories, $dSeparator);
        $source      = ($prepend) ? $directories : $this->namespaces[$namespace];
        $target      = ($prepend) ? $this->namespaces[$namespace] : $directories;

        $this->namespaces[$namespace] = array_unique(
            array_merge($source, $target)
        );

        return $this;
    }

    /**
     * Loads the class based on the class name
     *
     * @param string $name
     *
     * @return bool|mixed|string
     */
    public function autoload(string $name)
    {
        /**
         * Debug information
         */
        $this->debug = ['Loading: ' . $name];

        /**
         * Classes
         */
        if (isset($this->classes[$name])) {
            $file   = $this->classes[$name];
            $exists = $this->requireFile($file);
            if ($exists) {
                $this->debug[] = 'Class: load: ' . $file;
                return $file;
            }
        }

        $this->debug[] = 'Class: 404 : ' . $name;

        return $this->processLoadNameSpaces($name);
    }

    /**
     * Returns the registered classes array
     *
     * @return array
     */
    public function getClasses(): array
    {
        return $this->classes;
    }

    /**
     * Returns an array with debugging information after the last autoload
     *
     * @return array
     */
    public function getDebug()
    {
        return $this->debug;
    }

    /**
     * Returns the registered extensions array
     *
     * @return array
     */
    public function getExtensions(): array
    {
        return $this->extensions;
    }

    /**
     * Returns the registered files array
     *
     * @return array
     */
    public function getFiles(): array
    {
        return array_values($this->files);
    }

    /**
     * Returns the registered namespaces array
     *
     * @return array
     */
    public function getNamespaces(): array
    {
        return $this->namespaces;
    }

    /**
     * Checks if a file exists and then adds the file by doing virtual require
     */
    public function loadFiles(): void
    {
        foreach ($this->files as $file) {
            /**
             * Check if the file specified even exists
             */
            $this->requireFile($file);
        }
    }

    /**
     * Sets the classes for the loader. Overwrites existing entries
     *
     * @param array $classes
     *
     * @return Loader
     */
    public function setClasses(array $classes): Loader
    {
        $this->classes = [];
        foreach ($classes as $name => $file) {
            $this->addClass($name, $file);
        }

        return $this;
    }

    /**
     * Sets the extensions for the loader. Overwrites existing entries
     *
     * @param array $extensions
     *
     * @return Loader
     */
    public function setExtensions(array $extensions): Loader
    {
        $this->extensions = ['php'];
        foreach ($extensions as $extension) {
            $this->addExtension($extension);
        }

        return $this;
    }

    /**
     * Sets all the files that need to be loaded. Overwrites existing files
     *
     * @param array $files
     *
     * @return Loader
     */
    public function setFiles(array $files): Loader
    {
        $this->files = [];
        foreach ($files as $file) {
            $this->addFile($file);
        }

        return $this;
    }

    /**
     * Sets the namespaces for the loader. Overwrites existing entries
     *
     * @param array $namespaces
     *
     * @return Loader
     * @throws Exception
     */
    public function setNamespaces(array $namespaces): Loader
    {
        $this->namespaces = [];
        foreach ($namespaces as $namespace => $directories) {
            $this->addNamespace($namespace, $directories);
        }

        return $this;
    }

    /**
     * Registers this autoloader with SPL.
     *
     * @param bool $prepend True to prepend to the autoload stack.
     */
    public function register($prepend = false): void
    {
        if (!$this->isRegistered) {

            /**
             * Include all files that are registered
             */
            $this->loadFiles();

            spl_autoload_register(
                [$this, 'autoload'],
                true,
                (bool) $prepend
            );

            $this->isRegistered = true;
        }
    }

    /**
     * Unregisters this autoloader from SPL.
     */
    public function unregister(): void
    {
        if ($this->isRegistered) {
            spl_autoload_unregister([$this, 'autoload']);
            $this->isRegistered = false;
        }
    }
}
