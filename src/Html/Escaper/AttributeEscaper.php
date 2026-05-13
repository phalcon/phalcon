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

use function htmlspecialchars;
use function implode;
use function is_array;
use function rtrim;
use function trim;

/**
 * Escapes either a single attribute value (string) or an associative array
 * of attribute pairs. Boolean `true` becomes a bare key (e.g. `disabled`);
 * `false` and `null` skip the entry; arrays are joined with a space.
 */
class AttributeEscaper extends AbstractEscaper
{
    /**
     * @param array|string|null $input
     *
     * @return string
     */
    public function __invoke(mixed $input = null): string
    {
        return $this->escape($input);
    }

    /**
     * @param array|string|null $input
     *
     * @return string
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
     *
     * @param string $input
     *
     * @return string
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
