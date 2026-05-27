<?php

/**
 * This file is part of the Phalcon Framework.
 *
 * (c) Phalcon Team <team@phalcon.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * Implementation of this file has been influenced by AuraPHP
 * @link    https://github.com/auraphp/Aura.Html
 * @license https://github.com/auraphp/Aura.Html/blob/2.x/LICENSE
 */

declare(strict_types=1);

namespace Phalcon\Html\Helper;

/**
 * @property array $attributes
 * @property array $store
 */
abstract class AbstractSeries extends AbstractHelper
{
    /**
     * @var array
     */
    protected array $attributes = [];

    /**
     * @var array
     */
    protected array $store = [];

    /**
     * @param string      $indent
     * @param string|null $delimiter
     *
     * @return static
     */
    public function __invoke(
        string $indent = '    ',
        ?string $delimiter = null
    ): static {
        $this->delimiter = null === $delimiter ? PHP_EOL : $delimiter;
        $this->indent    = $indent;

        return $this;
    }

    /**
     * Generates and returns the HTML for the list. Entries are sorted by
     * their integer key first, so an asset registered with a lower position
     * renders before one registered with a higher position regardless of
     * registration order.
     *
     * @return string
     */
    public function __toString()
    {
        $sorted = $this->store;
        ksort($sorted);

        return $this->renderArrayElements(
            $sorted,
            $this->delimiter
        );
    }

    /**
     * Resets the internal store.
     *
     * @return $this
     */
    public function reset(): static
    {
        $this->store = [];

        return $this;
    }

    /**
     * Appends an entry to the store, optionally at a specific integer
     * position. When `$pos` is negative the entry is pushed onto the next
     * available auto-increment slot. When `$pos` is non-negative the entry
     * is placed at that key, advancing past any already-occupied slots so
     * existing entries are not overwritten. The store is ksort()ed in
     * `__toString`, so positions act as a sort key, not a strict address.
     *
     * @param array $entry
     * @param int   $pos
     */
    protected function pushOrPlace(array $entry, int $pos = -1): void
    {
        if ($pos < 0) {
            $this->store[] = $entry;

            return;
        }

        $key = $pos;
        while (isset($this->store[$key])) {
            $key++;
        }

        $this->store[$key] = $entry;
    }

    /**
     * Returns the tag name.
     *
     * @return string
     */
    abstract protected function getTag(): string;
}
