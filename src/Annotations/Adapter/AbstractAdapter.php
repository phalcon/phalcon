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
use Phalcon\Annotations\Reader;
use Phalcon\Annotations\ReaderInterface;
use Phalcon\Annotations\Reflection;

use function get_class;
use function is_object;
use function strcasecmp;

/**
 * This is the base class for Phalcon\Annotations adapters
 */
abstract class AbstractAdapter implements AdapterInterface
{
    /**
     * @var array
     */
    protected array $annotations = [];

    /**
     * @var Reader|null
     */
    protected ?Reader $reader = null;

    /**
     * Parses or retrieves all the annotations found in a class
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

        if (isset($this->annotations[$realClassName])) {
            return $this->annotations[$realClassName];
        }

        /**
         * Try to read the annotations from the adapter
         */
        $classAnnotations = $this->read($realClassName);

        if (null === $classAnnotations || false === $classAnnotations) {
            /**
             * Get the annotations reader
             */
            $reader            = $this->getReader();
            $parsedAnnotations = $reader->parse($realClassName);

            $classAnnotations                  = new Reflection($parsedAnnotations);
            $this->annotations[$realClassName] = $classAnnotations;
            $this->write($realClassName, $classAnnotations);
        }

        return $classAnnotations;
    }

    /**
     * Returns the annotations found in a specific constant
     *
     * @param string $className
     * @param string $constantName
     *
     * @return Collection
     */
    public function getConstant(
        string $className,
        string $constantName
    ): Collection {
        $constants = $this->getConstants($className);

        return $constants[$constantName] ?? new Collection();
    }

    /**
     * Returns the annotations found in all the class' constants
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
     * Returns the annotations found in a specific property
     *
     * @param string $className
     * @param string $propertyName
     *
     * @return Collection
     */
    public function getProperty(
        string $className,
        string $propertyName
    ): Collection {
        $properties = $this->get($className)->getPropertiesAnnotations();

        return $properties[$propertyName] ?? new Collection();
    }

    /**
     * Returns the annotations found in all the class' properties
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
     * Returns the annotations found in a specific method
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
     * Returns the annotations found in all the class' methods
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
     * Returns the annotation reader
     *
     * @return ReaderInterface
     */
    public function getReader(): ReaderInterface
    {
        return $this->reader ?? new Reader();
    }

    /**
     * Sets the annotations parser
     *
     * @param ReaderInterface $reader
     *
     * @return void
     */
    public function setReader(ReaderInterface $reader)
    {
        $this->reader = $reader;
    }
}
