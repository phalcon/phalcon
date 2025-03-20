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

use Phalcon\Events\Exception as EventsException;
use Phalcon\Events\Traits\EventsAwareTrait;
use Phalcon\Traits\Helper\Str\StartsWithTrait;

use function array_merge;
use function array_unique;
use function call_user_func;
use function hash;
use function is_callable;
use function is_string;
use function rtrim;
use function spl_autoload_register;
use function spl_autoload_unregister;
use function str_replace;
use function strlen;
use function substr;
use function trim;

use const DIRECTORY_SEPARATOR;

/**
 * The Phalcon Autoloader provides an easy way to automatically load classes
 * (namespaced or not) as well as files. It also features extension loading,
 * allowing the user to autoload files with different extensions than .php.
 *
 * @phpstan-type TNamespaces = array<string, TStrings>
 * @phpstan-type TStrings = array<string, string>
 */
class Loader
{
    use EventsAwareTrait;
    use StartsWithTrait;

    private const EVENT_AFTER_CHECK_CLASS  = "loader:afterCheckClass";
    private const EVENT_BEFORE_CHECK_CLASS = "loader:beforeCheckClass";
    private const EVENT_BEFORE_CHECK_PATH  = "loader:beforeCheckPath";
    private const EVENT_PATH_FOUND         = "loader:pathFound";

    /**
     * @var string|null
     */
    protected string | null $checkedPath = null;

    /**
     * @var TStrings
     */
    protected array $classes = [];

    /**
     * @var array<int, string>
     */
    protected array $debug = [];

    /**
     * @var TStrings
     */
    protected array $directories = [];

    /**
     * @var TStrings
     */
    protected array $extensions = [];

    /**
     * @var string|callable
     */
    protected $fileCheckingCallback = "is_file";

    /**
     * @var TStrings
     */
    protected array $files = [];

    /**
     * @var string|null
     */
    protected string | null $foundPath = null;

    /**
     * @var bool
     */
    protected bool $isDebug = false;

    /**
     * @var bool
     */
    protected bool $isRegistered = false;

    /**
     * @var TNamespaces
     */
    protected array $namespaces = [];

    /**
     * Loader constructor.
     */
    public function __construct(bool $isDebug = false)
    {
        $this->extensions = [$this->getHash('php') => 'php'];
        $this->isDebug    = $isDebug;
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
     * Adds a directory for the loaded files
     *
     * @param string $directory
     *
     * @return Loader
     */
    public function addDirectory(string $directory): Loader
    {
        $this->directories[$this->getHash($directory)] = $directory;

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
        $this->extensions[$this->getHash($extension)] = $extension;

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
        $this->files[$this->getHash($file)] = $file;

        return $this;
    }

    /**
     * @param string          $namespace
     * @param TStrings|string $directories
     * @param bool            $prepend
     *
     * @return $this
     */
    public function addNamespace(
        string $namespace,
        array | string $directories,
        bool $prepend = false
    ): Loader {
        $nsSeparator  = '\\';
        $dirSeparator = DIRECTORY_SEPARATOR;
        $namespace    = trim($namespace, $nsSeparator) . $nsSeparator;
        $directories  = $this->checkDirectories($directories, $dirSeparator);

        // initialize the namespace prefix array if needed
        if (!isset($this->namespaces[$namespace])) {
            $this->namespaces[$namespace] = [];
        }

        $source = ($prepend) ? $directories : $this->namespaces[$namespace];
        $target = ($prepend) ? $this->namespaces[$namespace] : $directories;

        $this->namespaces[$namespace] = array_unique(
            array_merge($source, $target)
        );

        return $this;
    }

    /**
     * Autoloads the registered classes
     *
     * @param string $className
     *
     * @return bool
     * @throws EventsException
     */
    public function autoload(string $className): bool
    {
        $this->debug = [];
        $this->addDebug("Loading: " . $className);
        $this->fireManagerEvent(self::EVENT_BEFORE_CHECK_CLASS, $className);

        if (true === $this->autoloadCheckClasses($className)) {
            return true;
        }

        $this->addDebug("Class: 404: " . $className);

        if (true === $this->autoloadCheckNamespaces($className)) {
            return true;
        }

        $this->addDebug("Namespace: 404: " . $className);

        if (
            true === $this->autoloadCheckDirectories(
                $this->directories,
                $className,
                true
            )
        ) {
            return true;
        }

        $this->addDebug("Directories: 404: " . $className);

        $this->fireManagerEvent(self::EVENT_AFTER_CHECK_CLASS, $className);

        /**
         * Cannot find the class, return false
         */
        return false;
    }

    /**
     * Get the path the loader is checking for a path
     *
     * @return string|null
     */
    public function getCheckedPath(): string | null
    {
        return $this->checkedPath;
    }

    /**
     * Returns the class-map currently registered in the autoloader
     *
     * @return string[]
     */
    public function getClasses(): array
    {
        return $this->classes;
    }

    /**
     * Returns debug information collected
     *
     * @return string[]
     */
    public function getDebug(): array
    {
        return $this->debug;
    }

    /**
     * Returns the directories currently registered in the autoloader
     *
     * @return string[]
     */
    public function getDirectories(): array
    {
        return $this->directories;
    }

    /**
     * Returns the file extensions registered in the loader
     *
     * @return string[]
     */
    public function getExtensions(): array
    {
        return $this->extensions;
    }

    /**
     * Returns the files currently registered in the autoloader
     *
     * @return string[]
     */
    public function getFiles(): array
    {
        return $this->files;
    }

    /**
     * Get the path when a class was found
     *
     * @return string|null
     */
    public function getFoundPath(): string | null
    {
        return $this->foundPath;
    }

    /**
     * Returns the namespaces currently registered in the autoloader
     *
     * @return TNamespaces
     */
    public function getNamespaces(): array
    {
        return $this->namespaces;
    }

    /**
     * Checks if a file exists and then adds the file by doing virtual require
     *
     * @return void
     * @throws EventsException
     */
    public function loadFiles(): void
    {
        foreach ($this->files as $file) {
            $this->fireManagerEvent(self::EVENT_BEFORE_CHECK_PATH, $file);

            if (true === $this->requireFile($file)) {
                $this->foundPath = $file;
                $this->fireManagerEvent(self::EVENT_PATH_FOUND, $file);
            }
        }
    }

    /**
     * Register the autoload method
     *
     * @param bool $prepend
     *
     * @return $this
     * @throws EventsException
     */
    public function register(bool $prepend = false): Loader
    {
        if (true !== $this->isRegistered) {
            $this->loadFiles();
            $this->registerAutoload($prepend);

            $this->isRegistered = true;
        }

        return $this;
    }

    /**
     * Register classes and their locations
     *
     * @param TStrings $classes
     * @param bool     $merge
     *
     * @return Loader
     */
    public function setClasses(array $classes, bool $merge = false): Loader
    {
        if (true !== $merge) {
            $this->classes = [];
        }

        foreach ($classes as $name => $class) {
            $this->addClass($name, $class);
        }

        return $this;
    }

    /**
     * Register directories in which "not found" classes could be found
     *
     * @param TStrings $directories
     * @param bool     $merge
     *
     * @return Loader
     */
    public function setDirectories(array $directories, bool $merge = false): Loader
    {
        return $this->addToCollection(
            $directories,
            "directories",
            "addDirectory",
            $merge
        );
    }

    /**
     * Sets an array of file extensions that the loader must try in each attempt
     * to locate the file
     *
     * @param TStrings $extensions
     * @param bool     $merge
     *
     * @return Loader
     */
    public function setExtensions(array $extensions, bool $merge = false): Loader
    {
        if (true !== $merge) {
            $this->extensions = [$this->getHash('php') => 'php'];
        }

        foreach ($extensions as $extension) {
            $this->addExtension($extension);
        }

        return $this;
    }

    /**
     * Sets the file check callback.
     *
     * ```php
     * // Default behavior.
     * $loader->setFileCheckingCallback("is_file");
     *
     * // Faster than `is_file()`, but implies some issues if
     * // the file is removed from the filesystem.
     * $loader->setFileCheckingCallback("stream_resolve_include_path");
     *
     * // Do not check file existence.
     * $loader->setFileCheckingCallback(null);
     * ```
     *
     * @param string|callable|null $method
     *
     * @return Loader
     * @throws Exception
     */
    public function setFileCheckingCallback(string | callable | null $method = null): Loader
    {
        if (is_callable($method) || is_string($method)) {
            $this->fileCheckingCallback = $method;
        } else {
            $this->fileCheckingCallback = function () {
                return true;
            };
        }

        return $this;
    }

    /**
     * Registers files that are "non-classes" hence need a "require". This is
     * very useful for including files that only have functions
     *
     * @param TStrings $files
     * @param bool     $merge
     *
     * @return Loader
     */
    public function setFiles(array $files, bool $merge = false): Loader
    {
        return $this->addToCollection(
            $files,
            "files",
            "addFile",
            $merge
        );
    }

    /**
     * Register namespaces and their related directories
     *
     * @param TNamespaces $namespaces
     * @param bool        $merge
     *
     * @return Loader
     */
    public function setNamespaces(array $namespaces, bool $merge = false): Loader
    {
        $dirSeparator = DIRECTORY_SEPARATOR;

        if (true !== $merge) {
            $this->namespaces = [];
        }

        foreach ($namespaces as $name => $directories) {
            $directories = $this->checkDirectories($directories, $dirSeparator);
            $this->addNamespace($name, $directories);
        }

        return $this;
    }

    /**
     * Unregister the autoload method
     *
     * @return Loader
     */
    public function unregister(): Loader
    {
        if (true === $this->isRegistered) {
            spl_autoload_unregister(
                [
                    $this,
                    "autoload",
                ]
            );

            $this->isRegistered = false;
        }

        return $this;
    }

    /**
     * If the file exists, require it and return true; false otherwise
     *
     * @param string $file The file to require
     *
     * @return bool
     * @throws EventsException
     */
    protected function requireFile(string $file): bool
    {
        /**
         * Check if the file specified even exists
         */
        if (false !== call_user_func($this->fileCheckingCallback, $file)) {
            /**
             * Call 'pathFound' event
             */
            $this->fireManagerEvent(self::EVENT_PATH_FOUND, $file);
            $this->addDebug("Require: " . $file);

            /**
             * Check if the file specified even exists
             */
            require_once $file;

            return true;
        }

        $this->addDebug("Require: 404: " . $file);

        return false;
    }

    /**
     * Adds a debugging message in the collection
     *
     * @param string $message
     */
    private function addDebug(string $message): void
    {
        if (true === $this->isDebug) {
            $this->debug[] = $message;
        }
    }

    /**
     * Traverses a collection and adds elements to it using the relevant
     * class method
     *
     * @param TStrings $collection
     * @param string   $collectionName
     * @param string   $method
     * @param bool     $merge
     *
     * @return Loader
     */
    private function addToCollection(
        array $collection,
        string $collectionName,
        string $method,
        bool $merge = false
    ): Loader {
        if (true !== $merge) {
            $this->$collectionName = [];
        }

        foreach ($collection as $element) {
            $this->$method($element);
        }

        return $this;
    }

    /**
     * Checks the registered classes to find the class. Includes the file if
     * found and returns true; false otherwise
     *
     * @param string $className
     *
     * @return bool
     * @throws EventsException
     */
    private function autoloadCheckClasses(string $className): bool
    {
        if (isset($this->classes[$className])) {
            $filePath = $this->classes[$className];
            $this->fireManagerEvent(self::EVENT_PATH_FOUND, $filePath);

            $this->requireFile($filePath);
            $this->addDebug("Class: load: " . $filePath);

            return true;
        }

        return false;
    }

    /**
     * Checks the registered directories to find the class. Includes the file if
     * found and returns true; false otherwise
     *
     * @param TStrings $directories
     * @param string   $className
     * @param bool     $isDirectory
     *
     * @return bool
     * @throws EventsException
     */
    private function autoloadCheckDirectories(
        array $directories,
        string $className,
        bool $isDirectory = false
    ): bool {
        $dirSeparator = DIRECTORY_SEPARATOR;
        $nsSeparator  = "\\";
        $className    = str_replace($nsSeparator, $dirSeparator, $className);

        foreach ($directories as $directory) {
            /**
             * Add a trailing directory separator if the user forgot to do that
             */
            $fixedDirectory = rtrim($directory, $dirSeparator) . $dirSeparator;

            foreach ($this->extensions as $extension) {
                /**
                 * Create a possible path for the file
                 */
                $filePath          = $fixedDirectory . $className . "." . $extension;
                $this->checkedPath = $filePath;
                $this->fireManagerEvent(self::EVENT_BEFORE_CHECK_PATH, $filePath);

                if (true === $this->requireFile($filePath)) {
                    if (true === $isDirectory) {
                        $this->addDebug("Directories: " . $filePath);
                    }
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Checks the registered namespaces to find the class. Includes the file if
     * found and returns true; false otherwise
     *
     * @param string $className
     *
     * @return bool
     * @throws EventsException
     */
    private function autoloadCheckNamespaces(string $className): bool
    {
        $nsSeparator = "\\";
        foreach ($this->namespaces as $prefix => $directories) {
            if (true !== $this->toStartsWith($className, $prefix)) {
                continue;
            }

            /**
             * Append the namespace separator to the prefix
             */
            $prefix   = rtrim($prefix, $nsSeparator) . $nsSeparator;
            $fileName = substr($className, strlen($prefix));

            if (true === $this->autoloadCheckDirectories($directories, $fileName)) {
                $this->addDebug("Namespace: " . $prefix . " - " . $this->checkedPath);
                return true;
            }
        }

        return false;
    }

    /**
     * Checks if the directories is an array or a string and throws an exception
     * if not. It converts the string to an array and then traverses the array
     * to normalize the directories with the proper directory separator at the
     * end
     *
     * @param TStrings|string $directories
     * @param string          $dirSeparator
     *
     * @return TStrings
     */
    private function checkDirectories(
        array | string $directories,
        string $dirSeparator
    ): array {
        if (is_string($directories)) {
            $directories = [$directories];
        }

        $results = [];
        foreach ($directories as $directory) {
            $directory = rtrim($directory, $dirSeparator) . $dirSeparator;

            $results[$this->getHash($directory)] = $directory;
        }

        return $results;
    }

    /**
     * @param string $content
     *
     * @return string
     */
    private function getHash(string $content): string
    {
        return hash('sha256', $content);
    }

    /**
     * @param bool $prepend
     *
     * @return bool
     */
    private function registerAutoload(bool $prepend): bool
    {
        return spl_autoload_register([$this, 'autoload'], true, $prepend);
    }
}
