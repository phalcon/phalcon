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

namespace Phalcon\Html\Helper;

use Phalcon\Traits\Helper\Str\InterpolateTrait;

use function array_key_last;
use function implode;
use function rtrim;

use const PHP_EOL;

/**
 * This component offers an easy way to create breadcrumbs for your application.
 * The resulting HTML when calling `render()` will have each breadcrumb enclosed
 * in `<li>` tags, while the whole string is enclosed in `<nav>` and `<ol>` tags.
 *
 * @phpstan-type TTemplate = array{
 *      main: string,
 *      line: string,
 *      last: string,
 * }
 *
 * @phpstan-type TElement = array{
 *      attributes: array<string, string>,
 *      icon: string,
 *      link: string,
 *      text: string,
 * }
 */
class Breadcrumbs extends AbstractHelper
{
    use InterpolateTrait;

    /**
     * @var string
     */
    protected string $delimiter = '';
    /**
     * @var string
     */
    protected string $indent = '    ';
    /**
     * @var array<string, string>
     */
    private array $attributes = [];
    /**
     * Keeps all the breadcrumbs
     *
     * @var array<int, TElement>
     */
    private array $data = [];
    /**
     * Crumb separator
     *
     * @var string
     */
    private string $separator = "<li>/</li>";
    /**
     * The HTML template to use to render the breadcrumbs.
     *
     * @var TTemplate
     */
    private array $template = [
        'main' => "
<nav%attributes%>
    <ol>
%items%
    </ol>
</nav>",
        'line' => '<li%attributes%><a href="%link%">%icon%%text%</a></li>',
        'last' => '<li><span%attributes%>%text%</span></li>',
    ];

    /**
     * Sets the indent and delimiter and returns the object back
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
     * Adds a new crumb.
     *
     * ```php
     * // Adding a crumb with a link
     * $breadcrumbs->add("Home", "/");
     *
     * // Adding a crumb with added attributes
     * $breadcrumbs->add("Home", "/", ["class" => "main"]);
     *
     * // Adding a crumb without a link (normally the last one)
     * $breadcrumbs->add("Users");
     * ```
     *
     * @param string                $text
     * @param string                $link
     * @param string                $icon
     * @param array<string, string> $attributes
     *
     * @return static
     */
    public function add(
        string $text,
        string $link = '',
        string $icon = '',
        array $attributes = []
    ): static {
        $count = count($this->data);
        $count++;
        $this->data[$count] = [
            'attributes' => $attributes,
            'icon'       => $icon,
            'link'       => $link,
            'text'       => $text,
        ];

        return $this;
    }

    /**
     * Clears the crumbs
     *
     * ```php
     * $breadcrumbs->clear()
     * ```
     */
    public function clear(): void
    {
        $this->data = [];
    }

    /**
     * Clear the attributes of the parent element
     *
     * @return $this
     */
    public function clearAttributes(): static
    {
        $this->attributes = [];

        return $this;
    }

    /**
     * Get the attributes of the parent element
     *
     * @return array<string, string>
     */
    public function getAttributes(): array
    {
        return $this->attributes;
    }

    /**
     * Returns the separator
     *
     * @return string
     */
    public function getSeparator(): string
    {
        return $this->separator;
    }

    /**
     * Return the current template
     *
     * @return TTemplate
     */
    public function getTemplate(): array
    {
        return $this->template;
    }

    /**
     * Removes crumb by url.
     *
     * ```php
     * // Remove the second element
     * $breadcrumbs->remove(2);
     * ```
     *
     * @param int $index
     *
     * @return void
     */
    public function remove(int $index): void
    {
        unset($this->data[$index]);
    }

    /**
     * Renders and outputs breadcrumbs based on previously set template.
     *
     * ```php
     * echo $breadcrumbs->render();
     * ```
     *
     * @return string
     */
    public function render(): string
    {
        /**
         * Early exit for empty data
         */
        if (true === empty($this->data)) {
            return '';
        }

        $output      = [];
        $lastUrl     = array_key_last($this->data);
        $lastElement = $this->data[$lastUrl];

        unset($this->data[$lastUrl]);

        foreach ($this->data as $element) {
            $output[] = $this->getLink($this->template['line'], $element);
        }

        /**
         * Last element
         */
        $output[] = $this->getLink($this->template['last'], $lastElement);

        return $this->toInterpolate(
            $this->template['main'],
            [
                'attributes' => $this->processAttributes($this->attributes),
                'delimiter'  => $this->delimiter,
                'indent'     => $this->indent,
                'items'      => implode(
                    $this->indent . $this->separator . $this->delimiter,
                    $output
                ),
            ]
        );
    }

    /**
     * Set the attributes for the parent element
     *
     * @param array<string, string> $attributes
     *
     * @return $this
     */
    public function setAttributes(array $attributes): static
    {
        $this->attributes = $attributes;

        return $this;
    }

    /**
     * Set the separator
     *
     * @param string $separator
     *
     * @return static
     */
    public function setSeparator(string $separator): static
    {
        $this->separator = $separator;

        return $this;
    }

    /**
     * Set the HTML template
     *
     * @param string $main
     * @param string $line
     * @param string $last
     *
     * @return static
     */
    public function setTemplate(
        string $main,
        string $line,
        string $last
    ): static {
        $this->template = [
            'main' => $main,
            'line' => $line,
            'last' => $last,
        ];

        return $this;
    }

    /**
     * Returns the internal breadcrumbs array
     *
     * @return array<int, TElement>
     */
    public function toArray(): array
    {
        return $this->data;
    }

    /**
     * @param string   $template
     * @param TElement $element
     *
     * @return string
     */
    private function getLink(
        string $template,
        array $element
    ): string {
        return $this->indent
            . $this->toInterpolate(
                $template,
                [
                    'attributes' => $this->processAttributes($element['attributes']),
                    'icon'       => $element['icon'],
                    'text'       => $this->escaper->html($element['text']),
                    'link'       => $element['link'],
                ]
            )
            . $this->delimiter;
    }

    /**
     * @param array<string, string> $attributes
     *
     * @return string
     */
    private function processAttributes(array $attributes): string
    {
        $attributes = $this->renderAttributes($attributes);

        return rtrim(!empty($attributes) ? ' ' . $attributes : '');
    }
}
