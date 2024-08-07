<?php

/**
 * This file is part of the Phalcon Framework.
 *
 * (c) Phalcon Team <team@phalcon.io>
 *
 * For the full copyright and license information, please view the LICENSE.txt
 * file that was distributed with this source code.
 *
 * Implementation of this file has been influenced by CapsulePHP
 *
 * @link    https://github.com/capsulephp/di
 * @license https://github.com/capsulephp/di/blob/3.x/LICENSE.md
 */

declare(strict_types=1);

namespace Phalcon\Container\Lazy;

use ArrayAccess;
use ArrayIterator;
use Countable;
use IteratorAggregate;
use Phalcon\Container\Container;

class ArrayValues extends AbstractLazy implements ArrayAccess, Countable, IteratorAggregate
{
    /**
     * @param array $values
     */
    public function __construct(
        protected array $values = []
    ) {
    }

    /**
     * @param Container $container
     *
     * @return array
     */
    public function __invoke(Container $container): array
    {
        return $this->resolveValues($container, $this->values);
    }

    /**
     * @return int
     */
    public function count(): int
    {
        return count($this->values);
    }

    /**
     * @return ArrayIterator
     */
    public function getIterator(): ArrayIterator
    {
        return new ArrayIterator($this->values);
    }

    /**
     * @param iterable $values
     *
     * @return void
     */
    public function merge(iterable $values): void
    {
        foreach ($values as $key => $value) {
            if (is_int($key)) {
                $this->values[] = $value;
            } else {
                $this->values[$key] = $value;
            }
        }
    }

    /**
     * @param int|string $offset
     */
    public function offsetExists(mixed $offset): bool
    {
        return array_key_exists($offset, $this->values);
    }

    /**
     * @param mixed $offset
     *
     * @return mixed
     */
    public function offsetGet(mixed $offset): mixed
    {
        return $this->values[$offset];
    }

    /**
     * @param mixed $offset
     * @param mixed $value
     *
     * @return void
     */
    public function offsetSet(mixed $offset, mixed $value): void
    {
        if ($offset === null) {
            $this->values[] = $value;
        } else {
            $this->values[$offset] = $value;
        }
    }

    /**
     * @param mixed $offset
     *
     * @return void
     */
    public function offsetUnset(mixed $offset): void
    {
        unset($this->values[$offset]);
    }

    /**
     * @param Container $container
     * @param mixed     $value
     *
     * @return mixed
     */
    protected function resolveValue(Container $container, mixed $value): mixed
    {
        if ($value instanceof AbstractLazy) {
            return $value($container);
        }

        if (is_array($value)) {
            return $this->resolveValues($container, $value);
        }

        return $value;
    }

    /**
     * @param Container $container
     * @param array     $values
     *
     * @return array
     */
    protected function resolveValues(Container $container, array $values): array
    {
        $return = [];

        foreach ($values as $key => $value) {
            $return[$key] = $this->resolveValue($container, $value);
        }

        return $return;
    }
}
