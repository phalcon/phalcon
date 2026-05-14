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

namespace Phalcon\Translate\Adapter;

use ArrayAccess;
use Exception as BaseException;
use Phalcon\Translate\Exceptions\ImmutableObject;
use Phalcon\Translate\InterpolatorFactory;

/**
 * @psalm-type TOptions array{
 *     defaultInterpolator?: string
 * }
 *
 * @template TKey of string
 * @template TValue of string
 * @implements ArrayAccess<TKey, TValue>
 */
abstract class AbstractAdapter implements AdapterInterface, ArrayAccess
{
    /**
     * @var string
     */
    protected string $defaultInterpolator = '';

    /**
     * AbstractAdapter constructor.
     *
     * @param TOptions            $options
     */
    public function __construct(
        protected InterpolatorFactory $interpolatorFactory,
        array $options = []
    ) {
        $this->defaultInterpolator = $options['defaultInterpolator'] ?? 'associativeArray';
    }

    /**
     * Returns the translation string of the given key (alias of method 't')
     *
     * @phpstan-param array<string, string> $placeholders
     *
     * @return string
     */
    public function _(string $translateKey, array $placeholders = []): string
    {
        return $this->query($translateKey, $placeholders);
    }

    /**
     * Check whether a translation key exists
     *
     * @param mixed $offset
     *
     * @return bool
     */
    public function offsetExists(mixed $offset): bool
    {
        /** @var string $offset */
        return $this->has($offset);
    }

    /**
     * Returns the translation related to the given key
     *
     * @param mixed $offset
     *
     * @return string
     */
    public function offsetGet(mixed $offset): mixed
    {
        /** @var string $offset */
        return $this->query($offset);
    }

    /**
     * Sets a translation value
     *
     * @param mixed $offset
     * @param mixed $value
     *
     * @return void
     * @throws ImmutableObject
     */
    public function offsetSet($offset, $value): void
    {
        throw new ImmutableObject();
    }

    /**
     * Unsets a translation from the dictionary
     *
     * @param mixed $offset
     *
     * @return void
     * @throws ImmutableObject
     */
    public function offsetUnset($offset): void
    {
        throw new ImmutableObject();
    }

    /**
     * Returns the translation string of the given key
     *
     * @phpstan-param array<string, string> $placeholders
     *
     * @return string
     */
    public function t(string $translateKey, array $placeholders = []): string
    {
        return $this->query($translateKey, $placeholders);
    }

    /**
     * Replaces placeholders by the values passed
     *
     * @phpstan-param array<string, string> $placeholders
     *
     * @return string
     * @throws BaseException
     */
    protected function replacePlaceholders(
        string $translation,
        array $placeholders = []
    ): string {
        $interpolator = $this->interpolatorFactory->newInstance($this->defaultInterpolator);

        return $interpolator->replacePlaceholders(
            $translation,
            $placeholders
        );
    }
}
