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

namespace Phalcon\Annotations;

use Phalcon\Annotations\Parser\Collection;
use Phalcon\Annotations\Parser\Reader;
use Phalcon\Annotations\Parser\ReaderInterface;
use Phalcon\Annotations\Parser\Reflection;
use Phalcon\Storage\Adapter\AdapterInterface;

class Annotations
{
    private const CACHE_PREFIX = '_PHATN';
    protected AdapterInterface $adapter;
    protected array $attributes = [];
    protected Reader | null $reader = null;

    public function __construct(AdapterInterface $adapter)
    {
        $this->adapter = $adapter;
    }

    /**
     * Parses or retrieves all the attributes found in a class
     *
     * @param mixed $className
     *
     * @return Reflection
     */
    public function get(mixed $className): Reflection
    {
        /**
         * Get the class name if it's an object
         */
        $realClassName = is_object($className) ? get_class($className) : $className;

        if (isset($this->attributes[$realClassName])) {
            return $this->attributes[$realClassName];
        }

        /**
         * Try to read the attributes from the adapter
         */
        $classAttributes = $this->read(self::CACHE_PREFIX . $realClassName);

        if (false === $classAttributes) {
            /**
             * Get the attributes reader
             */
            $reader           = $this->getReader();
            $parsedAttributes = $reader->parse($realClassName);

            $classAttributes                  = new Reflection($parsedAttributes);
            $this->attributes[$realClassName] = $classAttributes;
            $this->write(self::CACHE_PREFIX . $realClassName, $classAttributes);
        }

        return $classAttributes;
    }

    /**
     * Returns the attributes found in a specific constant
     *
     * @param string $className
     * @param string $constantName
     *
     * @return Collection
     */
    public function getConstant(string $className, string $constantName): Collection
    {
        $constants = $this->getConstants($className);

        return $constants[$constantName] ?? new Collection();
    }

    /**
     * Returns the attributes found in all the class' constants
     *
     * @param string $className
     *
     * @return array
     */
    public function getConstants(string $className): array
    {
        return $this->get($className)->getConstantsAnnotations();
    }

    /**
     * Returns the attributes found in a specific method
     *
     * @param string $className
     * @param string $methodName
     *
     * @return Collection
     */
    public function getMethod(string $className, string $methodName): Collection
    {
        $methods = $this->get($className)->getMethodsAnnotations();

        foreach ($methods as $methodKey => $method) {
            if (!strcasecmp($methodKey, $methodName)) {
                return $method;
            }
        }

        /**
         * Returns a collection anyway
         */
        return new Collection();
    }

    /**
     * Returns the attributes found in all the class' methods
     *
     * @param string $className
     *
     * @return array
     */
    public function getMethods(string $className): array
    {
        return $this->get($className)->getMethodsAnnotations();
    }

    /**
     * Returns the attributes found in all the class' properties
     *
     * @param string $className
     *
     * @return array
     */
    public function getProperties(string $className): array
    {
        return $this->get($className)->getPropertiesAnnotations();
    }

    /**
     * Returns the attributes found in a specific property
     *
     * @param string $className
     * @param string $propertyName
     *
     * @return Collection
     */
    public function getProperty(string $className, string $propertyName): Collection
    {
        $properties = $this->get($className)->getPropertiesAnnotations();

        return $properties[$propertyName] ?? new Collection();
    }

    /**
     * Returns the annotation reader
     *
     * @return ReaderInterface
     */
    public function getReader(): ReaderInterface
    {
        return $this->reader ?? new Reader();
    }

    /**
     * Reads parsed annotations from memory
     *
     * @param string $key
     *
     * @return Reflection|bool
     */
    public function read(string $key): Reflection | bool
    {
        return $this->adapter->get(strtolower($key)) ?? false;
    }

    /**
     * Sets the attributes parser
     *
     * @param ReaderInterface $reader
     *
     * @return void
     */
    public function setReader(ReaderInterface $reader): void
    {
        $this->reader = $reader;
    }

    /**
     * Writes parsed annotations to memory
     *
     * @param string     $key
     * @param Reflection $data
     *
     * @return bool
     */
    public function write(string $key, Reflection $data): bool
    {
        $this->adapter->set(strtolower($key), $data);

        return true;
    }
}
