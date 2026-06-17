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

namespace Phalcon\Html\Link\Serializer;

use function implode;
use function is_array;
use function is_bool;
use function str_replace;

/**
 * Class Phalcon\Http\Link\Serializer\Header
 */
class Header implements SerializerInterface
{
    /**
     * Serializes all the passed links to a HTTP link header
     *
     * @param array $links
     *
     * @return string|null
     */
    public function serialize(array $links): string | null
    {
        $elements = [];
        $result   = null;
        foreach ($links as $link) {
            /**
             * Leave templated links alone
             */
            if ($link->isTemplated()) {
                continue;
            }

            /**
             * Split the parts of the attributes so that we can parse them
             */
            $attributes = $link->getAttributes();
            $rels       = $link->getRels();
            $parts      = [
                '',
                'rel="' . implode(' ', $rels) . '"',
            ];

            foreach ($attributes as $key => $value) {
                if (is_array($value)) {
                    foreach ($value as $subValue) {
                        $parts[] = $key . '="' . $this->quote((string) $subValue) . '"';
                    }
                    continue;
                }

                if (!is_bool($value)) {
                    $parts[] = $key . '="' . $this->quote((string) $value) . '"';
                    continue;
                }

                if (true === $value) {
                    $parts[] = $key;
                }
            }

            $elements[] = '<'
                . $link->getHref()
                . '>'
                . implode('; ', $parts);
        }

        if (!empty($elements)) {
            $result = implode(",", $elements);
        }

        return $result;
    }

    /**
     * Escapes a quoted-string attribute value per RFC 8288 section 3: a
     * backslash and a double quote are each prefixed with a backslash so the
     * value cannot terminate or corrupt the header field.
     *
     * @param string $value
     *
     * @return string
     */
    private function quote(string $value): string
    {
        return str_replace(['\\', '"'], ['\\\\', '\\"'], $value);
    }
}
