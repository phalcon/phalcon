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
use Phalcon\Contracts\Annotations\AnnotationsTypes;
use Phalcon\Storage\Adapter\AdapterInterface;

/**
 * @phpstan-import-type annotations_attributes from AnnotationsTypes
 * @phpstan-import-type annotations_collection_map from AnnotationsTypes
 */
class Annotations
{
    private const CACHE_PREFIX = '_PHATN';
    protected AdapterInterface $adapter;
    /**
     * @phpstan-var annotations_attributes
     */
    protected array $attributes              = [];
    protected ReaderInterface | null $reader = null;

    public function __construct(AdapterInterface $adapter)
    {
        $this->adapter = $adapter;
    }

    /**
     * Parses or retrieves all the attributes found in a class
     *
     * @param object|string $className
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
     */
    public function getConstant(string $className, string $constantName): Collection
    {
        $constants = $this->getConstants($className);

        return $constants[$constantName] ?? new Collection();
    }

    /**
     * Returns the attributes found in all the class' constants
     *
     * @phpstan-return annotations_collection_map
     */
    public function getConstants(string $className): array
    {
        return $this->get($className)->getConstantsAnnotations();
    }

    /**
     * Returns the attributes found in a specific method
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
     * @phpstan-return annotations_collection_map
     */
    public function getMethods(string $className): array
    {
        return $this->get($className)->getMethodsAnnotations();
    }

    /**
     * Returns the attributes found in all the class' properties
     *
     * @phpstan-return annotations_collection_map
     */
    public function getProperties(string $className): array
    {
        return $this->get($className)->getPropertiesAnnotations();
    }

    /**
     * Returns the attributes found in a specific property
     */
    public function getProperty(string $className, string $propertyName): Collection
    {
        $properties = $this->get($className)->getPropertiesAnnotations();

        return $properties[$propertyName] ?? new Collection();
    }

    /**
     * Returns the annotation reader
     */
    public function getReader(): ReaderInterface
    {
        return $this->reader ?? new Reader();
    }

    /**
     * Reads parsed annotations from memory
     *
     * @phpstan-return Reflection|false
     */
    public function read(string $key): bool | Reflection
    {
        /** @var Reflection|null $data */
        $data = $this->adapter->get(strtolower($key));

        return $data ?? false;
    }

    /**
     * Sets the attributes parser
     */
    public function setReader(ReaderInterface $reader): void
    {
        $this->reader = $reader;
    }

    /**
     * Writes parsed annotations to memory
     */
    public function write(string $key, Reflection $data): bool
    {
        $this->adapter->set(strtolower($key), $data);

        return true;
    }
}
