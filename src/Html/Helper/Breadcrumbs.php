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

use function array_keys;
use function end;
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
 *      text: string,
 *      icon: string,
 *      attributes: array<string, string>,
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
     * @var array<string, TElement>
     */
    private array $data = [];
    /**
     * Crumb separator
     *
     * @var string
     */
    private string $separator = " / ";
    /**
     * The HTML template to use to render the breadcrumbs.
     *
     * @var TTemplate
     */
    private array $template = [
        'main' => '%indent%<nav%attributes%>%delimiter%'
            . '%indent%<ol>%delimiter%'
            . '%items%'
            . '%indent%</ol>%delimiter%'
            . '%indent%</nav>%delimiter%',
        'line' => '%indent%<li%attributes%>'
            . '<a href="%link%">%icon%%text%</a>'
            . '</li>%delimiter%',
        'last' => '%indent%<li><span%attributes%>%text%</span></li>%delimiter%',
    ];

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
     * @return $this
     */
    public function add(
        string $text,
        string $link = '',
        string $icon = '',
        array $attributes = []
    ): static {
        $this->data[$link] = [
            'text'       => $text,
            'icon'       => $icon,
            'attributes' => $attributes,
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
     * $breadcrumbs->remove("/admin/user/create");
     *
     * // remove a crumb without an url (last link)
     * $breadcrumbs->remove();
     * ```
     *
     * @param string $link
     */
    public function remove(string $link): void
    {
        unset($this->data[$link]);
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
        $urls        = array_keys($this->data);
        /** @var string $lastUrl */
        $lastUrl     = end($urls);
        $lastElement = $this->data[$lastUrl];

        unset($this->data[$lastUrl]);

        foreach ($this->data as $url => $element) {
            $output[] = $this->getLink(
                $this->template['line'],
                $element,
                $url
            );
        }

        /**
         * Last element
         */
        $output[] = $this->getLink(
            $this->template['last'],
            $lastElement,
            $lastUrl
        );

        $attributes = $this->renderAttributes($this->attributes);
        $attributes = rtrim(!empty($attributes) ? ' ' . $attributes : '');

        return $this->toInterpolate(
            $this->template['main'],
            [
                'attributes' => $attributes,
                'delimiter'  => $this->delimiter,
                'indent'     => $this->indent,
                'items'      => implode('', $output),
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
        string $last = ''
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
     * @return array<string, TElement>
     */
    public function toArray(): array
    {
        return $this->data;
    }

    /**
     * @param string   $template
     * @param TElement $element
     * @param string   $url
     *
     * @return string
     */
    private function getLink(
        string $template,
        array $element,
        string $url
    ): string {
        $icon       = trim($element['icon']);
        $icon       = !empty($icon) ? $icon . ' ' : '';
        $attributes = $this->renderAttributes($element['attributes']);
        $attributes = rtrim(!empty($attributes) ? ' ' . $attributes : '');

        return $this->toInterpolate(
            $template,
            [
                'attributes' => $attributes,
                'delimiter'  => $this->delimiter,
                'icon'       => $icon,
                'indent'     => $this->indent,
                'text'       => $this->escaper->html($element['text']),
                'link'       => $url,
            ]
        );
    }
}
