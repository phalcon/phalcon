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
 * Class AbstractSeries
 *
 * @property array $attributes
 * @property array $store
 */
abstract class AbstractSeries extends AbstractHelper
{
    /**
     * @var array
     */
    protected $attributes = [];

    /**
     * @var array
     */
    protected $store = [];

    /**
     * @param string $indent
     * @param string $delimiter
     *
     * @return AbstractSeries
     */
    public function __invoke(
        string $indent = null,
        string $delimiter = null
    ): AbstractSeries {
        if (null !== $delimiter) {
            $this->delimiter = $delimiter;
        }

        if (null !== $indent) {
            $this->indent = $indent;
        }

        $this->store = [];

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
     * Returns the tag name.
     *
     * @return string
     */
    abstract protected function getTag(): string;
}
