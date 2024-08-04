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

use Exception as BaseException;
use Phalcon\Translate\Exception;
use Phalcon\Translate\InterpolatorFactory;

/**
 * Class AbstractAdapter
 *
 * @package Phalcon\Translate\Adapter
 *
 * @property string              $defaultInterpolator
 * @property InterpolatorFactory $interpolatorFactory
 */
abstract class AbstractAdapter implements AdapterInterface
{
    /**
     * @var string
     */
    protected string $defaultInterpolator = '';

    /**
     * AbstractAdapter constructor.
     *
     * @param InterpolatorFactory  $interpolatorFactory
     * @param array<string, mixed> $options
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
     * @param string                $translateKey
     * @param array<string, string> $placeholders
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
        return $this->query($offset);
    }

    /**
     * Sets a translation value
     *
     * @param mixed $offset
     * @param mixed $value
     *
     * @throws Exception
     */
    public function offsetSet($offset, $value): void
    {
        throw new Exception('Translate is an immutable ArrayAccess object');
    }

    /**
     * Unsets a translation from the dictionary
     *
     * @param mixed $offset
     *
     * @throws Exception
     */
    public function offsetUnset($offset): void
    {
        throw new Exception('Translate is an immutable ArrayAccess object');
    }

    /**
     * Returns the translation string of the given key
     *
     * @param string                $translateKey
     * @param array<string, string> $placeholders
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
     * @param string                $translation
     * @param array<string, string> $placeholders
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
