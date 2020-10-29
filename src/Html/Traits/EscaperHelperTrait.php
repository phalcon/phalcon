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

namespace Phalcon\Escaper\Traits;

trait EscaperHelperTrait
{
    /**
     * Escapes a HTML attribute string
     *
     * @param string|null $attribute
     *
     * @return string
     */
    abstract public function attributes(string $attribute = null): string;

    /**
     * Escape CSS strings by replacing non-alphanumeric chars by their
     * hexadecimal escaped representation
     *
     * @param string $input
     *
     * @return string
     */
    abstract public function css(string $input): string;

    /**
     * Escape CSS strings by replacing non-alphanumeric chars by their
     * hexadecimal escaped representation
     *
     * @param string $input
     *
     * @return string
     */
    public function escapeCss(string $input): string
    {
        return $this->css($input);
    }

    /**
     * Escape JavaScript strings by replacing non-alphanumeric chars by their
     * hexadecimal escaped representation
     *
     * @param string $input
     *
     * @return string
     */
    public function escapeJs(string $input): string
    {
        return $this->js($input);
    }

    /**
     * Escapes a HTML string. Internally uses htmlspecialchars
     *
     * @param string|null $input
     *
     * @return string
     */
    public function escapeHtml(string $input = null): string
    {
        return $this->html($input);
    }

    /**
     * Escapes a HTML attribute string
     *
     * @param string|null $input
     *
     * @return string
     */
    public function escapeHtmlAttr(string $input = null): string
    {
        return $this->attributes($input);
    }

    /**
     * Escapes a URL. Internally uses rawurlencode
     *
     * @param string $input
     *
     * @return string
     */
    public function escapeUrl(string $input): string
    {
        return $this->url($input);
    }

    /**
     * Escapes a HTML string. Internally uses htmlspecialchars
     *
     * @param string|null $input
     *
     * @return string
     */
    abstract public function html(string $input = null): string;

    /**
     * Escape javascript strings by replacing non-alphanumeric chars by their
     * hexadecimal escaped representation
     *
     * @param string $input
     *
     * @return string
     */
    abstract public function js(string $input): string;

    /**
     * Escapes a URL. Internally uses rawurlencode
     *
     * @param string $input
     *
     * @return string
     */
    abstract public function url(string $input): string;
}
