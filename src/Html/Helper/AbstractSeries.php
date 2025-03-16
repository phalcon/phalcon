<?php

/**
 * This file is part of the Phalcon Framework.
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
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
     * @param string $indent
     * @param string $delimiter
     *
     * @return static
     */
    public function __invoke(
        string $indent = '    ',
        string $delimiter = PHP_EOL
    ): static {
        $this->delimiter = $delimiter;
        $this->indent    = $indent;
        $this->store     = [];

        return $this;
    }

    /**
     * Generates and returns the HTML for the list.
     *
     * @return string
     */
    public function __toString()
    {
        return $this->renderArrayElements(
            $this->store,
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
     * Returns the tag name.
     *
     * @return string
     */
    abstract protected function getTag(): string;
}
