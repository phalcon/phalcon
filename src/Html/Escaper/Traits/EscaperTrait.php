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

namespace Phalcon\Html\Escaper\Traits;

use function mb_convert_encoding;
use function mb_detect_encoding;

use const ENT_HTML401;
use const ENT_QUOTES;
use const ENT_SUBSTITUTE;

/**
 * Shared encoding/flags state and the encoding detection/normalization
 * utilities used by the per-context escaper objects (`HtmlEscaper`,
 * `AttributeEscaper`, `CssEscaper`, `JsEscaper`, `UrlEscaper`).
 */
trait EscaperTrait
{
    protected bool $doubleEncode = true;
    protected string $encoding   = 'utf-8';
    /**
     * ENT_QUOTES | ENT_SUBSTITUTE | ENT_HTML401
     */
    protected int $flags = ENT_QUOTES | ENT_SUBSTITUTE | ENT_HTML401;

    /**
     * Detects the character encoding of a string. Special-handling for
     * chr(172) and chr(128) to chr(159) which fail to be detected by
     * `mb_detect_encoding()`.
     */
    final public function detectEncoding(string $input): string | null
    {
        /**
         * An empty string is a basic charset. Return early, because the
         * strict detection below reports it as UTF-32.
         */
        if ('' === $input) {
            return 'ASCII';
        }

        foreach (['UTF-32', 'UTF-8', 'ISO-8859-1', 'ASCII'] as $charset) {
            if (false !== mb_detect_encoding($input, $charset, true)) {
                return $charset;
            }
        }

        return mb_detect_encoding($input) ?: null;
    }

    public function getDoubleEncode(): bool
    {
        return $this->doubleEncode;
    }

    public function getEncoding(): string
    {
        return $this->encoding;
    }

    public function getFlags(): int
    {
        return $this->flags;
    }

    /**
     * Normalizes a string's encoding to UTF-32, used by the CSS and JS
     * escapers before invoking the escape routines.
     */
    final public function normalizeEncoding(string $input): string
    {
        /** @phpstan-var string $converted */
        $converted = mb_convert_encoding(
            $input,
            'UTF-32',
            $this->detectEncoding($input)
        );

        return $converted;
    }

    public function setDoubleEncode(bool $doubleEncode): static
    {
        $this->doubleEncode = $doubleEncode;

        return $this;
    }

    public function setEncoding(string $encoding): static
    {
        $this->encoding = $encoding;

        return $this;
    }

    public function setFlags(int $flags): static
    {
        $this->flags = $flags;

        return $this;
    }
}
