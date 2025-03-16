<?php

/**
 * This file is part of the Phalcon Framework.
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Phalcon\Html\Helper;

use const PHP_EOL;

/**
 * Class AbstractList
 *
 * @package Phalcon\Html\Helper
 *
 * @property array  $attributes
 * @property string $elementTag
 * @property array  $store
 */
abstract class AbstractList extends AbstractHelper
{
    /**
     * @var array
     */
    protected array $attributes = [];

    /**
     * @var string
     */
    protected string $elementTag = 'li';

    /**
     * @var array
     */
    protected array $store = [];

    /**
     * @param string $indent
     * @param string $delimiter
     * @param array  $attributes
     *
     * @return static
     */
    public function __invoke(
        string $indent = '    ',
        string $delimiter = PHP_EOL,
        array $attributes = []
    ): static {
        $this->attributes = $attributes;
        $this->delimiter  = $delimiter;
        $this->indent     = $indent;
        $this->store      = [];

        return $this;
    }

    /**
     * Generates and returns the HTML for the list.
     *
     * @return string
     */
    public function __toString()
    {
        if (empty($this->store)) {
            return '';
        }

        $contents = $this->delimiter .
            $this->renderArrayElements(
                $this->store,
                $this->delimiter
            );

        return $this->renderFullElement(
            $this->getTag(),
            $contents,
            $this->attributes,
            true
        );
    }

    /**
     *
     * Returns the tag name.
     *
     * @return string
     *
     */
    abstract protected function getTag();
}
