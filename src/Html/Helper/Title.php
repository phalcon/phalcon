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

use function array_merge;
use function implode;

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
    protected $append = [];

    /**
     * @var array
     */
    protected $prepend = [];

    /**
     * @var string
     */
    protected $title = '';

    /**
     * @var string
     */
    protected $separator = '';

    /**
     * Sets the separator and returns the object back
     *
     * @param string      $separator
     * @param string|null $indent
     * @param string|null $delimiter
     *
     * @return Title
     */
    public function __invoke(
        string $separator = '',
        string $indent = null,
        string $delimiter = null
    ): Title {
        $this->delimiter = $delimiter;
        $this->indent    = $indent;
        $this->separator = $separator;

        return $this;
    }

    /**
     * Returns the title tags
     *
     * @return string
     * @throws Exception
     */
    public function __toString()
    {
        $items = array_merge(
            $this->prepend,
            [$this->title],
            $this->append
        );

        $indent    = $this->indent ? $this->indent : '';
        $delimiter = $this->delimiter ? $this->delimiter : '';

        $this->append  = [];
        $this->prepend = [];
        $this->title   = '';

        return $indent
            . $this->renderFullElement(
                'title',
                implode($this->separator, $items),
                [],
                true
            )
            . $delimiter;
    }

    /**
     * Appends text to current document title
     *
     * @param string $text
     * @param bool   $raw
     *
     * @return Title
     */
    public function append(string $text, bool $raw = false): Title
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
     * Sets the title
     *
     * @param string $text
     * @param bool   $raw
     *
     * @return Title
     */
    public function set(string $text, bool $raw = false): Title
    {
        $text = $raw ? $text : $this->escaper->html($text);

        $this->title = $text;

        return $this;
    }

    /**
     * Prepends text to current document title
     *
     * @param string $text
     * @param bool   $raw
     *
     * @return Title
     */
    public function prepend(string $text, bool $raw = false): Title
    {
        $text = $raw ? $text : $this->escaper->html($text);

        $this->prepend[] = $text;

        return $this;
    }

//    public static function checkField(var parameters) -> string
//    public static function endForm() -> string
//    public static function fileField(var parameters) -> string
//    public static function linkTo(parameters, text = null, local = true) -> string
//    public static function radioField(var parameters) -> string
//    public static function select(var parameters, data = null) -> string
//    public static function selectStatic(parameters, data = null) -> string
//    public static function tagHtml( string tagName, var parameters = null,
//          bool selfClose = false, bool onlyStart = false, bool useEol = false) -> string
//    public static function tagHtmlClose(string tagName, bool useEol = false) -> string
}
