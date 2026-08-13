<?php

/**
 * This file is part of the Phalcon Framework.
 *
 * (c) Phalcon Team <team@phalcon.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Phalcon\Support\Helper\Arr;

use Phalcon\Traits\Php\InfoTrait;

use function call_user_func;
use function is_array;
use function is_callable;
use function is_int;
use function is_object;
use function is_string;

/**
 * Groups the elements of an array based on the passed callable
 */
class Group
{
    use InfoTrait;

    /**
     * @param array<array-key, mixed> $collection
     * @param callable|string         $method
     *
     * @return array<array-key, list<mixed>>
     */
    public function __invoke(array $collection, callable | string $method): array
    {
        $filtered = [];
        foreach ($collection as $element) {
            $filtered = $this->processCallable($filtered, $method, $element);
            $filtered = $this->processObject($filtered, $method, $element);
            $filtered = $this->processOther($filtered, $method, $element);
        }

        return $filtered;
    }

    /**
     * @param callable|string $method
     *
     * @return bool
     *
     * @phpstan-assert-if-true callable $method
     */
    private function isCallable(callable | string $method): bool
    {
        return is_callable($method) || $this->phpFunctionExists($method);
    }

    /**
     * @param mixed $element
     *
     * @return bool
     *
     * @phpstan-assert-if-true object $element
     */
    private function isObject($element): bool
    {
        return is_object($element);
    }

    /**
     * @param mixed $method
     * @param mixed $element
     *
     * @return bool
     *
     * @phpstan-assert-if-true array<array-key, mixed> $element
     * @phpstan-assert-if-true array-key               $method
     */
    private function isSet($method, $element): bool
    {
        return (is_int($method) || is_string($method)) &&
            is_array($element) &&
            isset($element[$method]);
    }

    /**
     * @param array<array-key, list<mixed>> $filtered
     * @param callable|string               $method
     * @param mixed                         $element
     *
     * @return array<array-key, list<mixed>>
     */
    private function processCallable(array $filtered, callable | string $method, mixed $element): array
    {
        if (true === $this->isCallable($method)) {
            /** @var array-key $key */
            $key              = call_user_func($method, $element);
            $filtered[$key][] = $element;
        }

        return $filtered;
    }

    /**
     * @param array<array-key, list<mixed>> $filtered
     * @param callable|string               $method
     * @param mixed                         $element
     *
     * @return array<array-key, list<mixed>>
     */
    private function processObject(array $filtered, callable | string $method, mixed $element): array
    {
        if (
            true !== $this->isCallable($method) &&
            true === $this->isObject($element)
        ) {
            /** @var array-key $key */
            $key              = $element->$method;
            $filtered[$key][] = $element;
        }

        return $filtered;
    }

    /**
     * @param array<array-key, list<mixed>> $filtered
     * @param callable|string               $method
     * @param mixed                         $element
     *
     * @return array<array-key, list<mixed>>
     */
    private function processOther(array $filtered, callable | string $method, mixed $element): array
    {
        if (
            true !== $this->isCallable($method) &&
            true !== $this->isObject($element) &&
            true === $this->isSet($method, $element)
        ) {
            /** @var array-key $key */
            $key              = $element[$method];
            $filtered[$key][] = $element;
        }

        return $filtered;
    }
}
