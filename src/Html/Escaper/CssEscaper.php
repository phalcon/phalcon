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

/**
 * Escapes a string for use inside a CSS value by replacing non-alphanumeric
 * characters with their hexadecimal escape sequence.
 */
class CssEscaper extends AbstractEscaper
{
    /**
     * @param string $input
     *
     * @return string
     */
    public function __invoke(string $input): string
    {
        return $this->escape($input);
    }

    /**
     * @param string $input
     *
     * @return string
     */
    public function escape(string $input): string
    {
        if (empty($input)) {
            return '';
        }

        return $this->escapeMulti($this->normalizeEncoding($input), '\\', ' ', false);
    }
}
