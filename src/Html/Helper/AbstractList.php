<?php

/**
 * This file is part of the Phalcon Framework.
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Phalcon\Html\Helper;

use Phalcon\Html\Exception;

/**
 * Class AbstractList
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
    protected $attributes = [];

    /**
     * @var string
     */
    protected $elementTag = "li";

    /**
     * @var array
     */
    protected $store = [];

    /**
     * @param string $indent
     * @param string $delimiter
     * @param array  $attributes
     *
     * @return AbstractList
     */
    public function __invoke(
        string $indent = null,
        string $delimiter = null,
        array $attributes = []
    ): AbstractList {
        $this->attributes = $attributes;
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
     * @throws Exception
     */
    public function __toString()
    {
        if (empty($this->store)) {
            return "";
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
