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

use Phalcon\Html\Escaper\EscaperInterface;

use function htmlspecialchars;
use function mb_convert_encoding;
use function mb_detect_encoding;
use function rawurlencode;

use const ENT_HTML401;
use const ENT_QUOTES;
use const ENT_SUBSTITUTE;

/**
 * Phalcon\Escaper
 *
 * Escapes different kinds of text securing them. By using this component you
 * may prevent XSS attacks.
 *
 * This component only works with UTF-8. The PREG extension needs to be compiled
 * with UTF-8 support.
 *
 *```php
 * $escaper = new \Phalcon\Escaper();
 *
 * $escaped = $escaper->escapeCss("font-family: <Verdana>");
 *
 * echo $escaped; // font\2D family\3A \20 \3C Verdana\3E
 *```
 *
 * @property bool   $doubleEncode
 * @property string $encoding
 * @property int    $flags
 */
class Escaper implements EscaperInterface
{
    /**
     * @param string $encoding
     * @param int    $flags
     * @param bool   $doubleEncode
     */
    public function __construct(
        private string $encoding = 'utf-8',
        private int $flags = ENT_QUOTES | ENT_SUBSTITUTE | ENT_HTML401,
        private bool $doubleEncode = true
    ) {
    }

    /**
     * Escapes an HTML attribute string or array
     *
     * If the input is an array, the keys are the attribute names and the
     * values are attribute values. If a value is boolean (true/false) then
     * the attribute will have no value:
     * `['disabled' => true]`: `'disabled``
     *
     * The resulting string will have attribute pairs separated by a space.
     *
     * @param array|string $input
     *
     * @return string
     */
    public function attributes(mixed $input = null): string
    {
        if (!is_array($input)) {
            return $this->phpHtmlSpecialChars((string)$input);
        }

        $result = "";
        foreach ($input as $key => $value) {
            if (null === $value || false === $value) {
                continue;
            }

            $key = trim($key);

            if (is_array($value)) {
                $value = implode(" ", $value);
            }

            $result .= $this->phpHtmlSpecialChars($key);

            if (true !== $value) {
                $result .= "=\""
                    . $this->phpHtmlSpecialChars((string)$value)
                    . "\"";
            }

            $result .= " ";
        }

        return rtrim($result);
    }

    /**
     * Escape CSS strings by replacing non-alphanumeric chars by their
     * hexadecimal escaped representation
     *
     * @param string $input
     *
     * @return string
     */
    public function css(string $input): string
    {
        /**
         * Normalize encoding to UTF-32 and escape the string
         */
        return $this->doEscapeCss($this->normalizeEncoding($input));
    }

    /**
     * Detect the character encoding of a string to be handled by an encoder.
     * Special-handling for chr(172) and chr(128) to chr(159) which fail to be
     * detected by mb_detect_encoding()
     *
     * @param string $input
     *
     * @return string|null
     */
    final public function detectEncoding(string $input): string | null
    {
        /**
         * Strict encoding detection with fallback to non-strict detection.
         * Check encoding
         */
        $charsets = [
            'UTF-32',
            'UTF-8',
            'ISO-8859-1',
            'ASCII',
        ];
        foreach ($charsets as $charset) {
            if (false !== mb_detect_encoding($input, $charset, true)) {
                return $charset;
            }
        }

        /**
         * Fallback to global detection
         */
        return mb_detect_encoding($input);
    }

    /**
     * Returns the internal encoding used by the escaper
     *
     * @return string
     */
    public function getEncoding(): string
    {
        return $this->encoding;
    }

    /**
     * Returns the current flags for htmlspecialchars
     *
     * @return int
     */
    public function getFlags(): int
    {
        return $this->flags;
    }

    /**
     * Escapes an HTML string. Internally uses htmlspecialchars
     *
     * @param string|null $input
     *
     * @return string
     */
    public function html(string | null $input = null): string
    {
        if (null === $input) {
            return '';
        }
        return $this->phpHtmlSpecialChars($input);
    }

    /**
     * Escape javascript strings by replacing non-alphanumeric chars by their
     * hexadecimal escaped representation
     *
     * @param string $input
     *
     * @return string
     */
    public function js(string $input): string
    {
        /**
         * Normalize encoding to UTF-32
         * Escape the string
         */
        return $this->doEscapeJs($this->normalizeEncoding($input));
    }

    /**
     * Utility to normalize a string's encoding to UTF-32.
     *
     * @param string $input
     *
     * @return string
     */
    final public function normalizeEncoding(string $input): string
    {
        /**
         * Convert to UTF-32 (4 byte characters, regardless of actual number of
         * bytes in the character).
         */
        return mb_convert_encoding(
            $input,
            'UTF-32',
            $this->detectEncoding($input)
        );
    }

    /**
     * Sets the double_encode to be used by the escaper
     *
     *```php
     * $escaper->setDoubleEncode(false);
     *```
     *
     * @param bool $doubleEncode
     *
     * @return Escaper
     */
    public function setDoubleEncode(bool $doubleEncode): Escaper
    {
        $this->doubleEncode = $doubleEncode;

        return $this;
    }

    /**
     * Sets the encoding to be used by the escaper
     *
     *```php
     * $escaper->setEncoding("utf-8");
     *```
     *
     * @param string $encoding
     *
     * @return EscaperInterface
     */
    public function setEncoding(string $encoding): EscaperInterface
    {
        $this->encoding = $encoding;

        return $this;
    }

    /**
     * Sets the HTML quoting type for htmlspecialchars
     *
     *```php
     * $escaper->setFlags(ENT_XHTML);
     *```
     *
     * @param int $flags
     *
     * @return EscaperInterface
     */
    public function setFlags(int $flags): EscaperInterface
    {
        $this->flags = $flags;

        return $this;
    }

    /**
     * Sets the HTML quoting type for htmlspecialchars
     *
     *```php
     * $escaper->setHtmlQuoteType(ENT_XHTML);
     *```
     *
     * @param int $flags
     *
     * @return EscaperInterface
     */
    public function setHtmlQuoteType(int $flags): EscaperInterface
    {
        return $this->setFlags($flags);
    }

    /**
     * Escapes a URL. Internally uses rawurlencode
     *
     * @param string $input
     *
     * @return string
     */
    public function url(string $input): string
    {
        return rawurlencode($input);
    }

    /**
     * Proxy method for testing
     *
     * @param string $input
     *
     * @return string
     */
    protected function phpHtmlSpecialChars(string $input): string
    {
        return htmlspecialchars(
            $input,
            $this->flags,
            $this->encoding,
            $this->doubleEncode
        );
    }

    /**
     * @param string $input
     *
     * @return string
     */
    private function doEscapeCss(string $input): string
    {
        return $input;
    }

    /**
     * @param string $input
     *
     * @return string
     */
    private function doEscapeJs(string $input): string
    {
        return $input;
    }
}
