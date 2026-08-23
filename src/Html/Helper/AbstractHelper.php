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

use Phalcon\Contracts\Html\HtmlTypes;
use Phalcon\Html\Escaper\EscaperInterface;

use function array_intersect_key;
use function array_merge;
use function call_user_func_array;
use function is_string;
use function preg_replace;
use function rtrim;
use function str_repeat;
use function trim;

use const PHP_EOL;

/**
 * @phpstan-import-type html_attributes from HtmlTypes
 * @phpstan-import-type html_element_store from HtmlTypes
 */
abstract class AbstractHelper
{
    protected string $delimiter = PHP_EOL;
    protected string $indent = '    ';
    protected int $indentLevel = 1;

    /**
     * AbstractHelper constructor.
     */
    public function __construct(
        protected EscaperInterface $escaper,
        protected ?Doctype $doctype = null
    ) {
    }

    /**
     * Produces a closing tag
     */
    protected function close(string $tag, bool $raw = false): string
    {
        $tag = $raw ? $tag : $this->escapeName($tag);

        return '</' . $tag . '>';
    }

    /**
     * Removes the characters that end a tag or attribute name (white space,
     * "/", "=") and escapes the rest, so a crafted name cannot break out of
     * its position.
     */
    protected function escapeName(string $name): string
    {
        return $this->escaper->html(
            (string) preg_replace('~[\s/=]~', '', $name)
        );
    }

    /**
     * Replicates the indent x times as per indentLevel
     *
     * @return string
     */
    protected function indent(): string
    {
        return str_repeat($this->indent, $this->indentLevel);
    }

    /**
     * Forces `$key => $value` to the front of the attributes array,
     * removing any existing entry for that key. This guarantees the
     * attribute is always present and appears first in the rendered output.
     *
     * @phpstan-param html_attributes $attributes
     *
     * @phpstan-return html_attributes
     */
    protected function injectAttribute(
        string $key,
        string $value,
        array $attributes
    ): array {
        unset($attributes[$key]);

        return array_merge([$key => $value], $attributes);
    }

    /**
     * Keeps all the attributes sorted - same order all the time
     *
     * @phpstan-param html_attributes $overrides
     * @phpstan-param html_attributes $attributes
     *
     * @phpstan-return html_attributes
     */
    protected function orderAttributes(
        array $overrides,
        array $attributes
    ): array {
        $order = [
            'rel'    => null,
            'type'   => null,
            'for'    => null,
            'src'    => null,
            'href'   => null,
            'action' => null,
            'id'     => null,
            'name'   => null,
            'value'  => null,
            'class'  => null,
        ];

        $intersect = array_intersect_key($order, $attributes);
        $results   = array_merge($intersect, $attributes);
        $results   = array_merge($overrides, $results);

        /**
         * Just in case remove the 'escape' attribute
         */
        unset($results['escape']);

        return $results;
    }

    /**
     * Traverses an array and calls the method defined in the first element
     * with attributes as the second, returning the resulting string
     *
     * @phpstan-param html_element_store $elements
     */
    protected function renderArrayElements(
        array $elements,
        string $delimiter
    ): string {
        $result = '';
        foreach ($elements as $item) {
            $result .= $item[2]
                . call_user_func_array([$this, $item[0]], $item[1])
                . $delimiter;
        }

        return $result;
    }

    /**
     * Renders all the attributes
     *
     * @phpstan-param html_attributes $attributes
     */
    protected function renderAttributes(array $attributes): string
    {
        $result = '';
        foreach ($attributes as $key => $value) {
            if (is_string($key) && null !== $value) {
                $key = $this->escapeName($key);

                if (true === $value) {
                    $result .= $key . ' ';
                } else {
                    $result .= $key
                        . '="'
                        . $this->escaper->attributes($value)
                        . '" ';
                }
            }
        }

        return $result;
    }

    /**
     * Renders an element
     *
     * @phpstan-param html_attributes $attributes
     */
    protected function renderElement(
        string $tag,
        array $attributes = []
    ): string {
        return $this->renderTag($tag, $attributes);
    }

    /**
     * Renders an element
     *
     * @phpstan-param html_attributes $attributes
     */
    protected function renderFullElement(
        string $tag,
        string $text,
        array $attributes = [],
        bool $raw = false
    ): string {
        $content = $raw ? $text : $this->escaper->html($text);

        return $this->renderElement($tag, $attributes) .
            $content .
            $this->close($tag, $raw);
    }

    /**
     * Renders a tag
     *
     * @phpstan-param html_attributes $attributes
     */
    protected function renderTag(
        string $tag,
        array $attributes = [],
        string $close = ''
    ): string {
        $escapedAttrs = '';
        if (!empty($attributes)) {
            $attrs        = $this->orderAttributes([], $attributes);
            $escapedAttrs = ' ' . rtrim($this->renderAttributes($attrs));
        }

        $localClose = empty(trim($close)) ? '' : ' ' . trim($close);

        return '<' . $this->escapeName($tag) . $escapedAttrs . $localClose . '>';
    }

    /**
     * Produces a self close tag i.e. <img />
     *
     * @phpstan-param html_attributes $attributes
     */
    protected function selfClose(string $tag, array $attributes = []): string
    {
        return $this->renderTag($tag, $attributes, '/');
    }
}
