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

use Phalcon\Html\Escaper\Traits\EscaperTrait;

use function chr;
use function ctype_alnum;
use function dechex;
use function strlen;
use function substr;
use function unpack;

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
    use EscaperTrait;

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
