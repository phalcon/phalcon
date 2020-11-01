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

namespace Phalcon\Html;

use function array_keys;
use function end;
use function implode;
use function str_replace;

/**
 * Phalcon\Html\Breadcrumbs
 *
 * This component offers an easy way to create breadcrumbs for your application.
 * The resulting HTML when calling `render()` will have each breadcrumb enclosed
 * in `<dt>` tags, while the whole string is enclosed in `<dl>` tags.
 *
 * @property array  $data
 * @property string $separator
 * @property string $template
 */
class Breadcrumbs
{
    /**
     * Keeps all the breadcrumbs
     *
     * @var array
     */
    private $data = [];

    /**
     * Crumb separator
     *
     * @var string
     */
    private $separator = " / ";

    /**
     * The HTML template to use to render the breadcrumbs.
     *
     * @var string
     */
    private $template = '<dt><a href="{link}">{label}</a></dt>';

    /**
     * Adds a new crumb.
     *
     * ```php
     * // Adding a crumb with a link
     * $breadcrumbs->add("Home", "/");
     *
     * // Adding a crumb without a link (normally the last one)
     * $breadcrumbs->add("Users");
     * ```
     *
     * @param string $label
     * @param string $link
     *
     * @return Breadcrumbs
     */
    public function add(string $label, string $link = ''): Breadcrumbs
    {
        $this->data[$link] = $label;

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
     * Returns the separator
     *
     * @return string
     */
    public function getSeparator(): string
    {
        return $this->separator;
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
        $output    = [];
        $urls      = array_keys($this->data);
        $lastUrl   = end($urls);
        $lastLabel = $this->data[$lastUrl];

        unset($this->data[$lastUrl]);

        foreach ($this->data as $url => $element) {
            $output[] = $this->getLink($element, $url);
        }

        /**
         * Check if this is the "Home" element i.e. count() = 0
         */
        $line = '<dt>' . $lastLabel . '</dt>';
        if (true === empty($this->data)) {
            $line = $this->getLink($lastLabel, $lastUrl);
        }

        $output[] = $line;

        return '<dl>'
            . implode('<dt>' . $this->separator . '</dt>', $output)
            . '</dl>';
    }

    /**
     * Set the separator
     *
     * @param string $separator
     *
     * @return Breadcrumbs
     */
    public function setSeparator(string $separator): Breadcrumbs
    {
        $this->separator = $separator;

        return $this;
    }

    /**
     * Returns the internal breadcrumbs array
     *
     * @return array
     */
    public function toArray(): array
    {
        return $this->data;
    }

    /**
     * @param string $label
     * @param string $url
     *
     * @return string
     */
    private function getLink(string $label, string $url): string
    {
        return str_replace(
            [
                '{label}',
                '{link}',
            ],
            [
                $label,
                $url,
            ],
            $this->template
        );
    }
}
