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

namespace Phalcon\Annotations\Adapter;

use Phalcon\Annotations\Collection;
use Phalcon\Annotations\Exception;
use Phalcon\Annotations\Reader;
use Phalcon\Annotations\ReaderInterface;
use Phalcon\Annotations\Reflection;
use Phalcon\Contracts\Annotations\AnnotationsTypes;

use function count;
use function get_class;
use function is_object;
use function strcasecmp;

/**
 * This is the base class for Phalcon\Annotations adapters
 *
 * The adapters supply read() and write(). This class does not declare them,
 * the same as in cphalcon.
 *
 * @method mixed read(string $key)
 * @method mixed write(string $key, Reflection $data)
 *
 * @phpstan-import-type annotations_cache from AnnotationsTypes
 * @phpstan-import-type annotations_collection_map from AnnotationsTypes
 */
abstract class AbstractAdapter implements AdapterInterface
{
    /**
     * @phpstan-var annotations_cache
     */
    protected array $annotations = [];

    /**
     * Maximum number of class annotation entries retained in the
     * in-memory cache. 0 (default) keeps the original unbounded
     * behavior; a positive value clears the cache when adding a new
     * class would exceed it.
     */
    protected int $annotationsLimit = 0;

    protected ReaderInterface | null $reader = null;

    /**
     * Parses or retrieves all the annotations found in a class
     *
     * @throws Exception
     * @throws \ReflectionException
     *
     * @phpstan-param object|string $className
     */
    public function get(mixed $className): Reflection
    {
        /**
         * Get the class name if it's an object
         */
        if (is_object($className)) {
            $realClassName = get_class($className);
        } else {
            /** @var class-string $className */
            $realClassName = $className;
        }

        if (isset($this->annotations[$realClassName])) {
            return $this->annotations[$realClassName];
        }

        /**
         * Try to read the annotations from the adapter
         */
        $classAnnotations = $this->read($realClassName);

        if ($classAnnotations === null || $classAnnotations === false) {
            /**
             * Get the annotations reader
             */
            $reader            = $this->getReader();
            $parsedAnnotations = $reader->parse($realClassName);

            if (
                $this->annotationsLimit > 0 &&
                count($this->annotations) >= $this->annotationsLimit
            ) {
                $this->annotations = [];
            }

            $classAnnotations                  = new Reflection($parsedAnnotations);
            $this->annotations[$realClassName] = $classAnnotations;
            $this->write($realClassName, $classAnnotations);
        }

        /** @var Reflection $classAnnotations */
        return $classAnnotations;
    }

    /**
     * Returns the configured annotations-cache cap (0 = unlimited).
     * See setAnnotationsLimit().
     */
    public function getAnnotationsLimit(): int
    {
        return $this->annotationsLimit;
    }

    /**
     * Returns the annotations found in a specific constant
     *
     * @throws Exception
     * @throws \ReflectionException
     */
    public function getConstant(string $className, string $constantName): Collection
    {
        $constants = $this->getConstants($className);

        /**
         * Returns a collection anyways
         */
        return $constants[$constantName] ?? new Collection();
    }

    /**
     * Returns the annotations found in all the class' constants
     *
     * @throws Exception
     * @throws \ReflectionException
     *
     * @phpstan-return annotations_collection_map
     */
    public function getConstants(string $className): array
    {
        /**
         * Get the full annotations from the class
         */
        $classAnnotations = $this->get($className);

        return $classAnnotations->getConstantsAnnotations();
    }

    /**
     * Returns the annotations found in a specific method
     *
     * @throws Exception
     * @throws \ReflectionException
     */
    public function getMethod(string $className, string $methodName): Collection
    {
        /**
         * Get the full annotations from the class
         */
        $classAnnotations = $this->get($className);

        $methods = $classAnnotations->getMethodsAnnotations();

        if (isset($methods[$methodName])) {
            return $methods[$methodName];
        }

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
     * Returns the annotations found in all the class' methods
     *
     * @throws Exception
     * @throws \ReflectionException
     *
     * @phpstan-return annotations_collection_map
     */
    public function getMethods(string $className): array
    {
        /**
         * Get the full annotations from the class
         */
        $classAnnotations = $this->get($className);

        return $classAnnotations->getMethodsAnnotations();
    }

    /**
     * Returns the annotations found in all the class' properties
     *
     * @throws Exception
     * @throws \ReflectionException
     *
     * @phpstan-return annotations_collection_map
     */
    public function getProperties(string $className): array
    {
        /**
         * Get the full annotations from the class
         */
        $classAnnotations = $this->get($className);

        return $classAnnotations->getPropertiesAnnotations();
    }

    /**
     * Returns the annotations found in a specific property
     *
     * @throws Exception
     * @throws \ReflectionException
     */
    public function getProperty(string $className, string $propertyName): Collection
    {
        /**
         * Get the full annotations from the class
         */
        $classAnnotations = $this->get($className);

        $properties = $classAnnotations->getPropertiesAnnotations();

        /**
         * Returns a collection anyways
         */
        return $properties[$propertyName] ?? new Collection();
    }

    /**
     * Returns the annotation reader
     */
    public function getReader(): ReaderInterface
    {
        if (null === $this->reader) {
            $this->reader = new Reader();
        }

        return $this->reader;
    }

    /**
     * Caps the number of class entries retained in the annotations
     * cache. 0 disables the cap (the default; preserves the original
     * unbounded behavior). When the cap is exceeded, the cache is
     * cleared and repopulated on subsequent reads.
     *
     * @return void
     */
    public function setAnnotationsLimit(int $annotationsLimit)
    {
        $this->annotationsLimit = $annotationsLimit;
    }

    /**
     * Sets the annotations parser
     *
     * @return void
     */
    public function setReader(ReaderInterface $reader)
    {
        $this->reader = $reader;
    }
}
