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
use Phalcon\Contracts\Translate\TranslateTypes;
use Phalcon\Translate\Exception;
use Phalcon\Translate\Exceptions\ImmutableObject;
use Phalcon\Translate\Exceptions\KeyNotFound;
use Phalcon\Translate\Interpolator\InterpolatorInterface;
use Phalcon\Translate\InterpolatorFactory;

/**
 * @phpstan-import-type translate_adapter_options from TranslateTypes
 * @phpstan-import-type translate_placeholders from TranslateTypes
 *
 * @implements ArrayAccess<string, string>
 */
abstract class AbstractAdapter implements AdapterInterface, ArrayAccess
{
    protected string $defaultInterpolator = '';
    protected InterpolatorInterface | null $interpolator = null;
    protected bool $triggerError = false;

    /**
     * AbstractAdapter constructor.
     *
     * @phpstan-param translate_adapter_options $options
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
     * @phpstan-param translate_placeholders $placeholders
     */
    public function _(string $translateKey, array $placeholders = []): string
    {
        return $this->query($translateKey, $placeholders);
    }

    /**
     * Whenever a key is not found this method will be called
     *
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
     */
    public function offsetExists(mixed $offset): bool
    {
        /** @var string $offset */
        return $this->has($offset);
    }

    /**
     * Returns the translation related to the given key
     *
     * @param string $offset
     *
     * @return string
     */
    public function offsetGet(mixed $offset): string
    {
        return $this->query($offset);
    }

    /**
     * Sets a translation value
     *
     * @throws ImmutableObject
     */
    public function offsetSet($offset, $value): void
    {
        throw new ImmutableObject();
    }

    /**
     * Unsets a translation from the dictionary
     *
     * @throws ImmutableObject
     */
    public function offsetUnset($offset): void
    {
        throw new ImmutableObject();
    }

    /**
     * Returns the translation string of the given key
     *
     * @phpstan-param translate_placeholders $placeholders
     */
    public function t(string $translateKey, array $placeholders = []): string
    {
        return $this->query($translateKey, $placeholders);
    }

    /**
     * Replaces placeholders by the values passed
     *
     * @phpstan-param translate_placeholders $placeholders
     *
     * @throws Exception
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
