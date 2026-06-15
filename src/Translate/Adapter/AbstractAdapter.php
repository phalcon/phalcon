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
use Phalcon\Translate\Exceptions\KeyNotFound;
use Phalcon\Translate\InterpolatorFactory;
use Phalcon\Translate\Interpolator\InterpolatorInterface;

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
     * @var InterpolatorInterface | null
     */
    protected InterpolatorInterface | null $interpolator = null;

    /**
     * @var bool
     */
    protected bool $triggerError = false;

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
        $this->triggerError        = (bool)($options['triggerError'] ?? false);
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
     * Whenever a key is not found this method will be called
     *
     * @param string $index
     *
     * @return string
     * @throws KeyNotFound
     */
    public function notFound(string $index): string
    {
        if (true === $this->triggerError) {
            throw new KeyNotFound($index);
        }

        return $index;
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
        if (null === $this->interpolator) {
            $this->interpolator = $this->interpolatorFactory->newInstance(
                $this->defaultInterpolator
            );
        }

        return $this->interpolator->replacePlaceholders(
            $translation,
            $placeholders
        );
    }
}
