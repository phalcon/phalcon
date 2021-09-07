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

namespace Phalcon\Filter;

use function is_array;
use function is_string;

/**
 * Lazy loads, stores and exposes sanitizer objects
 *
 * @property array $mapper
 * @property array $services
 */
class Filter implements FilterInterface
{
    public const FILTER_ABSINT      = 'absint';
    public const FILTER_ALNUM       = 'alnum';
    public const FILTER_ALPHA       = 'alpha';
    public const FILTER_BOOL        = 'bool';
    public const FILTER_EMAIL       = 'email';
    public const FILTER_FLOAT       = 'float';
    public const FILTER_INT         = 'int';
    public const FILTER_LOWER       = 'lower';
    public const FILTER_LOWERFIRST  = 'lowerFirst';
    public const FILTER_REGEX       = 'regex';
    public const FILTER_REMOVE      = 'remove';
    public const FILTER_REPLACE     = 'replace';
    public const FILTER_SPECIAL     = 'special';
    public const FILTER_SPECIALFULL = 'specialFull';
    public const FILTER_STRING      = 'string';
    public const FILTER_STRIPTAGS   = 'striptags';
    public const FILTER_TRIM        = 'trim';
    public const FILTER_UPPER       = 'upper';
    public const FILTER_UPPERFIRST  = 'upperFirst';
    public const FILTER_UPPERWORDS  = 'upperWords';
    public const FILTER_URL         = 'url';

    /**
     * @var array
     */
    protected array $mapper = [];

    /**
     * @var array
     */
    protected array $services = [];

    /**
     * Filter constructor.
     *
     * @param array $mapper
     */
    public function __construct(array $mapper = [])
    {
        $this->init($mapper);
    }

    /**
     * Get a service. If it is not in the mapper array, create a new object,
     * set it and then return it.
     *
     * @param string $name
     *
     * @return mixed
     * @throws Exception
     */
    public function get(string $name)
    {
        if (true !== isset($this->mapper[$name])) {
            throw new Exception(
                'The service ' . $name . ' has not been found in the locator'
            );
        }

        if (true !== isset($this->services[$name])) {
            $definition            = $this->mapper[$name];
            $this->services[$name] = $this->createInstance($definition);
        }

        return $this->services[$name];
    }

    /**
     * Checks if a service exists in the map array
     *
     * @param string $name
     *
     * @return bool
     */
    public function has(string $name): bool
    {
        return isset($this->mapper[$name]);
    }

    /**
     * Sanitizes a value with a specified single or set of sanitizers
     *
     * @param mixed $value
     * @param mixed $sanitizers
     * @param bool  $noRecursive
     *
     * @return array|false|mixed|null
     * @throws Exception
     */
    public function sanitize($value, $sanitizers, bool $noRecursive = false)
    {
        /**
         * First we need to figure out whether this is one sanitizer (string) or
         * an array with different sanitizers in it.
         *
         * All is well if the sanitizer accepts only one parameter, but certain
         * sanitizers require more than one parameter. To figure this out we
         * need to of course call call_user_func_array() but with the correct
         * parameters.
         *
         * If the array is passed with just values then those are just the
         * sanitizer names i.e.
         *
         * $locator->sanitize( 'abcde', ['trim', 'upper'])
         *
         * If the sanitizer requires more than one parameter then we need to
         * inject those parameters in the sanitize also:
         *
         * $locator->sanitize(
         *     '  mary had a little lamb ',
         *     [
         *         'trim',
         *         'replace' => [' ', '-'],
         *         'remove'  => ['mary'],
         *     ]
         * );
         *
         * The above should produce "-had-a-little-lamb"
         */

        /**
         * Filter is an array
         */
        if (true === is_array($sanitizers)) {
            return $this->processArraySanitizers($sanitizers, $value, $noRecursive);
        }

        /**
         * Apply a single sanitizer to the values. Check if the values are an
         * array
         */
        if (true === is_array($value) && true !== $noRecursive) {
            return $this->processArrayValues($value, $sanitizers);
        }

        /**
         * One value one sanitizer
         */
        return $this->sanitizer($value, $sanitizers);
    }

    /**
     * Set a new service to the mapper array
     *
     * @param string $name
     * @param mixed  $service
     */
    public function set(string $name, $service): void
    {
        $this->mapper[$name] = $service;

        unset($this->services[$name]);
    }

    /**
     * Loads the objects in the internal mapper array
     *
     * @param array $mapper
     */
    protected function init(array $mapper): void
    {
        foreach ($mapper as $name => $service) {
            $this->set($name, $service);
        }
    }

    /**
     * @param mixed $definition
     *
     * @return mixed
     */
    private function createInstance($definition)
    {
        if (true === is_string($definition)) {
            return new $definition();
        }

        return $definition;
    }

    /**
     * @param array $sanitizers
     * @param mixed $value
     * @param bool  $noRecursive
     *
     * @return array|false|mixed|null
     * @throws Exception
     */
    private function processArraySanitizers(
        array $sanitizers,
        $value,
        bool $noRecursive
    ) {
        /**
         * Null value - return immediately
         */
        if (null === $value) {
            return $value;
        }

        /**
         * `value` is something. Loop through the sanitizers
         */
        foreach ($sanitizers as $sanitizerKey => $sanitizer) {
            /**
             * If `sanitizer` is an array, that means that the sanitizerKey
             * is the name of the sanitizer.
             */
            [$sanitizerName, $sanitizerParams] = $this->splitSanitizerParameters(
                $sanitizerKey,
                $sanitizer
            );

            /**
             * Check if the value is an array of elements. If `noRecursive`
             * has been defined it is a straight up; otherwise recursion is
             * required
             */
            $value = $this->processValueIsArray(
                $value,
                $sanitizerName,
                $sanitizerParams,
                $noRecursive
            );
            $value = $this->processValueIsNotArray(
                $value,
                $sanitizerName,
                $sanitizerParams
            );
        }

        return $value;
    }

    /**
     * Processes the array values with the relevant sanitizers
     *
     * @param array  $values
     * @param string $sanitizerName
     * @param array  $sanitizerParams
     *
     * @return array
     * @throws Exception
     */
    private function processArrayValues(
        array $values,
        string $sanitizerName,
        array $sanitizerParams = []
    ): array {
        $arrayValue = [];

        foreach ($values as $itemKey => $itemValue) {
            $arrayValue[$itemKey] = $this->sanitizer(
                $itemValue,
                $sanitizerName,
                $sanitizerParams
            );
        }

        return $arrayValue;
    }

    /**
     * Internal sanitize wrapper for recursion
     *
     * @param mixed  $value
     * @param string $sanitizerName
     * @param array  $sanitizerParams
     *
     * @return false|mixed
     * @throws Exception
     */
    private function sanitizer(
        $value,
        string $sanitizerName,
        array $sanitizerParams = []
    ) {

        if (true !== $this->has($sanitizerName)) {
            if (true !== empty($sanitizerName)) {
                trigger_error(
                    'Sanitizer "' . $sanitizerName . '" is not registered',
                    E_USER_NOTICE
                );
            }

            return $value;
        }

        $sanitizerObject = $this->get($sanitizerName);
        $params          = array_merge([$value], $sanitizerParams);

        return call_user_func_array($sanitizerObject, $params);
    }

    /**
     * @param mixed  $value
     * @param string $sanitizerName
     * @param array  $sanitizerParams
     * @param bool   $noRecursive
     *
     * @return array|mixed
     * @throws Exception
     */
    private function processValueIsArray(
        $value,
        string $sanitizerName,
        array $sanitizerParams,
        bool $noRecursive
    ) {
        if (true === is_array($value) && true !== $noRecursive) {
            $value = $this->processArrayValues(
                $value,
                $sanitizerName,
                $sanitizerParams
            );
        }

        return $value;
    }

    /**
     * @param mixed  $value
     * @param string $sanitizerName
     * @param array  $sanitizerParams
     *
     * @return array|false|mixed
     * @throws Exception
     */
    private function processValueIsNotArray(
        $value,
        string $sanitizerName,
        array $sanitizerParams
    ) {
        if (true !== is_array($value)) {
            $value = $this->sanitizer(
                $value,
                $sanitizerName,
                $sanitizerParams
            );
        }

        return $value;
    }

    /**
     * @param mixed $sanitizerKey
     * @param mixed $sanitizer
     *
     * @return array
     */
    private function splitSanitizerParameters($sanitizerKey, $sanitizer): array
    {
        /**
         * If `sanitizer` is an array, that means that the sanitizerKey
         * is the name of the sanitizer.
         */
        if (true === is_array($sanitizer)) {
            return [$sanitizerKey, $sanitizer];
        }

        return [$sanitizer, []];
    }
}
