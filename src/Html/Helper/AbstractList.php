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

use const PHP_EOL;

/**
 * Class AbstractList
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
     * @param string      $indent
     * @param string|null $delimiter
     * @param array       $attributes
     *
     * @return static
     */
    public function __invoke(
        string $indent = '    ',
        ?string $delimiter = null,
        array $attributes = []
    ): static {
        $this->attributes = $attributes;
        $this->delimiter  = null === $delimiter ? PHP_EOL : $delimiter;
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
