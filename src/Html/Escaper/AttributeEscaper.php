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

use Phalcon\Contracts\Html\HtmlTypes;

use function htmlspecialchars;
use function implode;
use function is_array;
use function preg_replace;
use function rtrim;
use function trim;

/**
 * Escapes either a single attribute value (string) or an associative array
 * of attribute pairs. Boolean `true` becomes a bare key (e.g. `disabled`);
 * `false` and `null` skip the entry; arrays are joined with a space.
 *
 * @phpstan-import-type html_escaper_input from HtmlTypes
 */
class AttributeEscaper extends AbstractEscaper
{
    /**
     * @phpstan-param html_escaper_input $input
     */
    public function __invoke(mixed $input = null): string
    {
        return $this->escape($input);
    }

    /**
     * @phpstan-param html_escaper_input $input
     */
    public function escape(mixed $input = null): string
    {
        if (!is_array($input)) {
            if (null === $input) {
                return '';
            }

            return $this->escapeValue((string) $input);
        }

        $result = '';
        foreach ($input as $key => $value) {
            if (null === $value || false === $value) {
                continue;
            }

            $key = trim($key);

            /**
             * The key is an attribute name. Remove the characters that end an
             * attribute name (white space, "/", "=") so a crafted key cannot
             * add more attributes.
             */
            $key = preg_replace('~[\s/=]~', '', $key);

            if (is_array($value)) {
                $value = implode(' ', $value);
            }

            $result .= $this->escapeValue((string) $key);

            if (true !== $value) {
                $result .= '="'
                    . $this->escapeValue((string) $value)
                    . '"';
            }

            $result .= ' ';
        }

        return rtrim($result);
    }

    /**
     * Encodes a single key/value via `htmlspecialchars`.
     */
    protected function escapeValue(string $input): string
    {
        return htmlspecialchars(
            $input,
            $this->flags,
            $this->encoding,
            $this->doubleEncode
        );
    }
}
