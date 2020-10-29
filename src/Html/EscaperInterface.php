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

namespace Phalcon\Escaper;

/**
 * Interface for Phalcon\Escaper
 */
interface EscaperInterface
{
    /**
     * Escape CSS strings by replacing non-alphanumeric chars by their
     * hexadecimal representation
     *
     * @param string $input
     *
     * @return string
     */
    public function escapeCss(string $input): string;

    /**
     * Escapes a HTML string
     *
     * @param string $input
     *
     * @return string
     */
    public function escapeHtml(string $input): string;

    /**
     * Escapes a HTML attribute string
     *
     * @param string $input
     *
     * @return string
     */
    public function escapeHtmlAttr(string $input): string;

    /**
     * Escape Javascript strings by replacing non-alphanumeric chars by their
     * hexadecimal representation
     *
     * @param string $input
     *
     * @return string
     */
    public function escapeJs(string $input): string;

    /**
     * Escapes a URL. Internally uses rawurlencode
     *
     * @param string $input
     *
     * @return string
     */
    public function escapeUrl(string $input): string;

    /**
     * Returns the internal encoding used by the escaper
     *
     * @return string
     */
    public function getEncoding(): string;

    /**
     * Sets the encoding to be used by the escaper
     *
     * @param string $encoding
     */
    public function setEncoding(string $encoding): void;

    /**
     * Sets the HTML quoting type for htmlspecialchars
     *
     * @param int $flags
     */
    public function setHtmlQuoteType(int $flags): void;
}
