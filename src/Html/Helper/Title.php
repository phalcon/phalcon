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

use function array_merge;
use function implode;

use const PHP_EOL;

/**
 * Class Title
 *
 * @property array  $append
 * @property string $delimiter
 * @property string $indent
 * @property array  $prepend
 * @property string $title
 * @property string $separator
 */
class Title extends AbstractHelper
{
    /**
     * @var array
     */
    protected array $append = [];

    /**
     * @var array
     */
    protected array $prepend = [];

    /**
     * @var string
     */
    protected string $separator = '';

    /**
     * @var string
     */
    protected string $title = '';

    /**
     * Sets the separator and returns the object back
     *
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

        return $this;
    }

    /**
     * Returns the title tags
     *
     * @return string
     */
    public function __toString()
    {
        $items = array_merge(
            $this->prepend,
            [$this->title],
            $this->append
        );

        $this->append  = [];
        $this->prepend = [];
        $this->title   = '';

        return $this->indent
            . $this->renderFullElement(
                'title',
                implode($this->separator, $items),
                [],
                true
            )
            . $this->delimiter;
    }

    /**
     * Appends text to current document title
     *
     * @param string $text
     * @param bool   $raw
     *
     * @return Title
     */
    public function append(string $text, bool $raw = false): static
    {
        $text = $raw ? $text : $this->escaper->html($text);

        $this->append[] = $text;

        return $this;
    }

    /**
     * Returns the title
     *
     * @return string
     */
    public function get(): string
    {
        return $this->title;
    }

    /**
     * Prepends text to current document title
     *
     * @param string $text
     * @param bool   $raw
     *
     * @return static
     */
    public function prepend(string $text, bool $raw = false): static
    {
        $text = $raw ? $text : $this->escaper->html($text);

        array_unshift($this->prepend, $text);

        return $this;
    }

    /**
     * Sets the title
     *
     * @param string $text
     * @param bool   $raw
     *
     * @return static
     */
    public function set(string $text, bool $raw = false): static
    {
        $text = $raw ? $text : $this->escaper->html($text);

        $this->title = $text;

        return $this;
    }

    /**
     * Sets the separator
     *
     * @param string $separator
     * @param bool   $raw
     *
     * @return static
     */
    public function setSeparator(string $separator, bool $raw = false): static
    {
        $this->separator = $raw ? $separator : $this->escaper->html($separator);

        return $this;
    }
}
