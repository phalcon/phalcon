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
use function is_callable;
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
     * @return array<array-key, mixed>
     */
    public function __invoke(array $collection, $method): array
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
     * @param mixed $method
     *
     * @return bool
     */
    private function isCallable($method): bool
    {
        return is_callable($method) ||
            (is_string($method) && $this->phpFunctionExists($method));
    }

    /**
     * @param mixed $element
     *
     * @return bool
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
     */
    private function isSet($method, $element): bool
    {
        return isset($element[$method]);
    }

    /**
     * @param array<array-key, mixed> $filtered
     * @param callable|string         $method
     * @param mixed                   $element
     *
     * @return array<array-key, mixed>
     */
    private function processCallable(array $filtered, $method, $element): array
    {
        if (true === $this->isCallable($method)) {
            /** @var string $key */
            $key              = call_user_func($method, $element);
            $filtered[$key][] = $element;
        }

        return $filtered;
    }

    /**
     * @param array<array-key, mixed> $filtered
     * @param callable|string         $method
     * @param mixed                   $element
     *
     * @return array<array-key, mixed>
     */
    private function processObject(array $filtered, $method, $element): array
    {
        if (
            true !== $this->isCallable($method) &&
            true === $this->isObject($element)
        ) {
            $filtered[$element->$method][] = $element;
        }

        return $filtered;
    }

    /**
     * @param array<array-key, mixed> $filtered
     * @param callable|string         $method
     * @param mixed                   $element
     *
     * @return array<array-key, mixed>
     */
    private function processOther(array $filtered, $method, $element): array
    {
        if (
            true !== $this->isCallable($method) &&
            true !== $this->isObject($element) &&
            true === $this->isSet($method, $element)
        ) {
            $filtered[$element[$method]][] = $element;
        }

        return $filtered;
    }
}
