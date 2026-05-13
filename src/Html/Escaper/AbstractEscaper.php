<?php

/**
 * This file is part of the Phalcon Framework.
 *
 * (c) Phalcon Team <team@phalcon.io>
 *
 * For the full copyright and license information, please view the LICENSE.txt
 * file that was distributed with this source code.
 *
 * Implementation of this file has been influenced by AuraPHP
 * @link    https://github.com/auraphp/Aura.Html
 * @license https://github.com/auraphp/Aura.Html/blob/2.x/LICENSE
 */

declare(strict_types=1);

namespace Phalcon\Html\Escaper;

use function chr;
use function ctype_alnum;
use function dechex;
use function mb_convert_encoding;
use function mb_detect_encoding;
use function strlen;
use function substr;
use function unpack;

use const ENT_HTML401;
use const ENT_QUOTES;
use const ENT_SUBSTITUTE;

/**
 * Shared base for the per-context escaper objects. Holds the encoding,
 * htmlspecialchars flag, and double-encode toggle, plus the encoding
 * detection / normalization utilities used by the CSS and JS escapers.
 *
 * Each concrete context (`HtmlEscaper`, `AttributeEscaper`, `CssEscaper`,
 * `JsEscaper`, `UrlEscaper`) extends this so that callers can configure
 * one context without affecting the others.
 *
 * @property bool   $doubleEncode
 * @property string $encoding
 * @property int    $flags
 */
abstract class AbstractEscaper
{
    /**
     * @var bool
     */
    protected bool $doubleEncode = true;

    /**
     * @var string
     */
    protected string $encoding = 'utf-8';

    /**
     * ENT_QUOTES | ENT_SUBSTITUTE | ENT_HTML401
     *
     * @var int
     */
    protected int $flags = ENT_QUOTES | ENT_SUBSTITUTE | ENT_HTML401;

    /**
     * Detects the character encoding of a string. Special-handling for
     * chr(172) and chr(128) to chr(159) which fail to be detected by
     * `mb_detect_encoding()`.
     *
     * @param string $input
     *
     * @return string|null
     */
    final public function detectEncoding(string $input): string | null
    {
        foreach (['UTF-32', 'UTF-8', 'ISO-8859-1', 'ASCII'] as $charset) {
            if (false !== mb_detect_encoding($input, $charset, true)) {
                return $charset;
            }
        }

        return mb_detect_encoding($input) ?: null;
    }

    /**
     * @return bool
     */
    public function getDoubleEncode(): bool
    {
        return $this->doubleEncode;
    }

    /**
     * @return string
     */
    public function getEncoding(): string
    {
        return $this->encoding;
    }

    /**
     * @return int
     */
    public function getFlags(): int
    {
        return $this->flags;
    }

    /**
     * Normalizes a string's encoding to UTF-32, used by the CSS and JS
     * escapers before invoking the escape routines.
     *
     * @param string $input
     *
     * @return string
     */
    final public function normalizeEncoding(string $input): string
    {
        return mb_convert_encoding(
            $input,
            'UTF-32',
            $this->detectEncoding($input)
        );
    }

    /**
     * @param bool $doubleEncode
     *
     * @return static
     */
    public function setDoubleEncode(bool $doubleEncode): static
    {
        $this->doubleEncode = $doubleEncode;

        return $this;
    }

    /**
     * @param string $encoding
     *
     * @return static
     */
    public function setEncoding(string $encoding): static
    {
        $this->encoding = $encoding;

        return $this;
    }

    /**
     * @param int $flags
     *
     * @return static
     */
    public function setFlags(int $flags): static
    {
        $this->flags = $flags;

        return $this;
    }

    /**
     * Perform escaping of non-alphanumeric characters to different formats.
     *
     * @param string $input       UTF-32 encoded string
     * @param string $escapeChar  Escape prefix (e.g. '\' for CSS, '\x' for JS)
     * @param string $escapeExtra Character appended after hex (e.g. ' ' for CSS)
     * @param bool   $whitelist   Whether to allow a JS-specific whitelist through
     *
     * @return string
     */
    protected function escapeMulti(
        string $input,
        string $escapeChar,
        string $escapeExtra,
        bool $whitelist
    ): string {
        if (empty($input)) {
            return '';
        }

        $len    = strlen($input);
        $offset = 0;
        $format = 'N';

        if ($len >= 4) {
            $bom = substr($input, 0, 4);
            if ("\x00\x00\xFE\xFF" === $bom) {
                $offset = 4;
            } elseif ("\xFF\xFE\x00\x00" === $bom) {
                $offset = 4;
                $format = 'V';
            }
        }

        if (($len - $offset) % 4 !== 0) {
            return '';
        }

        $result = '';

        for ($i = $offset; $i < $len; $i += 4) {
            $unpacked = unpack($format, substr($input, $i, 4));
            $value    = $unpacked[1];

            if (0 === $value) {
                break;
            }

            if ($value < 123 && ctype_alnum(chr($value))) {
                $result .= chr($value);
                continue;
            }

            if ($whitelist) {
                switch ($value) {
                    case 0x20:
                    case 0x21:
                    case 0x23:
                    case 0x24:
                    case 0x28:
                    case 0x29:
                    case 0x2A:
                    case 0x2B:
                    case 0x2C:
                    case 0x2D:
                    case 0x2E:
                    case 0x2F:
                    case 0x3A:
                    case 0x3B:
                    case 0x3F:
                    case 0x5B:
                    case 0x5C:
                    case 0x5D:
                    case 0x5E:
                    case 0x5F:
                    case 0x7B:
                    case 0x7C:
                    case 0x7D:
                    case 0x09:
                    case 0x0A:
                        $result .= chr($value);
                        continue 2;
                }
            }

            $result .= $escapeChar . dechex($value) . $escapeExtra;
        }

        return $result;
    }
}
