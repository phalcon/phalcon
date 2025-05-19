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

use function call_user_func_array;
use function is_array;
use function is_string;

/**
 * Lazy loads, stores and exposes sanitizer objects
 *
 *
 * @method int         absint(mixed $input)
 * @method string      alnum(mixed $input)
 * @method string      alpha(mixed $input)
 * @method bool        bool(mixed $input)
 * @method string      email(string $input)
 * @method float       float(mixed $input)
 * @method int         int(string $input)
 * @method string      lower(string $input)
 * @method string      lowerfirst(string $input)
 * @method mixed       regex(mixed $input, mixed $pattern, mixed $replace)
 * @method mixed       remove(mixed $input, mixed $replace)
 * @method mixed       replace(mixed $input, mixed $source, mixed $target)
 * @method string      special(string $input)
 * @method string      specialfull(string $input)
 * @method string      string(string $input)
 * @method string      striptags(string $input)
 * @method string      trim(string $input)
 * @method string      upper(string $input)
 * @method string      upperFirst(string $input)
 * @method string|null upperWords(string $input)
 * @method string|null url(string $input)
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
    public const FILTER_LOWERFIRST  = 'lowerfirst';
    public const FILTER_REGEX       = 'regex';
    public const FILTER_REMOVE      = 'remove';
    public const FILTER_REPLACE     = 'replace';
    public const FILTER_SPECIAL     = 'special';
    public const FILTER_SPECIALFULL = 'specialfull';
    public const FILTER_STRING      = 'string';
    public const FILTER_STRIPTAGS   = 'striptags';
    public const FILTER_TRIM        = 'trim';
    public const FILTER_UPPER       = 'upper';
    public const FILTER_UPPERFIRST  = 'upperfirst';
    public const FILTER_UPPERWORDS  = 'upperwords';
    public const FILTER_URL         = 'url';

    /**
     * @var array<string, string>
     */
    protected array $mapper = [];

    /**
     * @var array<string, FilterInterface>
     */
    protected array $services = [];

    /**
     * Filter constructor.
     *
     * @param array<string, string> $mapper
     */
    public function __construct(array $mapper = [])
    {
        $this->init($mapper);
    }

    /**
     * Magic call to make the helper objects available as methods.
     *
     * @param string               $name
     * @param array<string, mixed> $args
     *
     * @return mixed
     * @throws Exception
     */
    public function __call(string $name, array $args)
    {
        $sanitizer = $this->get($name);

        return call_user_func_array([$sanitizer, "__invoke"], $args);
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
    public function get(string $name): mixed
    {
        if (!isset($this->mapper[$name])) {
            throw new Exception(
                'Filter ' . $name . ' is not registered'
            );
        }

        if (!isset($this->services[$name])) {
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
     * @param mixed                                 $value
     * @param array<array-key, string|array>|string $sanitizers
     * @param bool                                  $noRecursive
     *
     * @return array|false|mixed|null
     * @throws Exception
     */
    public function sanitize(
        mixed $value,
        array | string $sanitizers,
        bool $noRecursive = false
    ): mixed {
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
        if (is_array($sanitizers)) {
            return $this->processArraySanitizers($sanitizers, $value, $noRecursive);
        }

        /**
         * Apply a single sanitizer to the values. Check if the values are an
         * array
         */
        if (is_array($value) && true !== $noRecursive) {
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
    public function set(string $name, mixed $service): void
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
        if (is_string($definition)) {
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
        mixed $value,
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
            if (is_array($value)) {
                $value = $this->processValueIsArray(
                    $value,
                    $sanitizerName,
                    $sanitizerParams,
                    $noRecursive
                );
            } else {
                $value = $this->processValueIsNotArray(
                    $value,
                    $sanitizerName,
                    $sanitizerParams
                );
            }
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
     * @param mixed  $value
     * @param string $sanitizerName
     * @param array  $sanitizerParams
     * @param bool   $noRecursive
     *
     * @return array|mixed
     * @throws Exception
     */
    private function processValueIsArray(
        mixed $value,
        string $sanitizerName,
        array $sanitizerParams,
        bool $noRecursive
    ) {
        if ($noRecursive) {
            $value = $this->sanitizer(
                $value,
                $sanitizerName,
                $sanitizerParams
            );
        } else {
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
        mixed $value,
        string $sanitizerName,
        array $sanitizerParams
    ) {
        if (!is_array($value)) {
            $value = $this->sanitizer(
                $value,
                $sanitizerName,
                $sanitizerParams
            );
        }

        return $value;
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
            if (!empty($sanitizerName)) {
                trigger_error(
                    "Sanitizer '" . $sanitizerName . "' is not registered"
                );
            }

            return $value;
        }

        $sanitizerObject = $this->get($sanitizerName);
        $params          = array_merge([$value], $sanitizerParams);

        return call_user_func_array($sanitizerObject, $params);
    }

    /**
     * @param mixed $sanitizerKey
     * @param mixed $sanitizer
     *
     * @return array
     */
    private function splitSanitizerParameters(mixed $sanitizerKey, mixed $sanitizer): array
    {
        /**
         * If `sanitizer` is an array, that means that the sanitizerKey
         * is the name of the sanitizer.
         */
        if (is_array($sanitizer)) {
            return [$sanitizerKey, $sanitizer];
        }

        return [$sanitizer, []];
    }
}
