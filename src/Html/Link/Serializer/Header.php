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
                    $parts = $this->processArray($parts, $value, $key);
                    continue;
                }

                if (!is_bool($value)) {
                    $parts[] = $key . '="' . $value . '"';
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
     * Traverses a value array and add the parts
     *
     * @param array  $parts
     * @param array  $value
     * @param string $key
     *
     * @return array
     */
    private function processArray(array $parts, array $value, string $key): array
    {
        foreach ($value as $subValue) {
            $parts[] = $key . '="' . $subValue . '"';
        }

        return $parts;
    }
}
