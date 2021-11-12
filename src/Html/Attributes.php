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

use Phalcon\Support\Collection;
use Phalcon\Html\Attributes\RenderInterface;

/**
 * This class helps to work with HTML Attributes
 */
class Attributes extends Collection implements RenderInterface
{
    /**
     * Render attributes as HTML attributes
     */
    public function render(): string
    {
        return $this->renderAttributes("", $this->toArray());
    }

    /**
     * Alias of the render method
     */
    public function __toString(): string
    {
        return $this->render();
    }

    /**
     * @param string $code
     * @param array  $attributes
     *
     * @return string
     * @throws Exception
     *
     * @todo Move this in a trait and start using the escaper
     */
    private function renderAttributes(string $code, array $attributes): string
    {
        $order = [
            'rel'    => null,
            'type'   => null,
            'for'    => null,
            'src'    => null,
            'href'   => null,
            'action' => null,
            'id'     => null,
            'name'   => null,
            'value'  => null,
            'class'  => null,
        ];

        $attrs = [];
        foreach ($order as $key => $value) {
            if (true === isset($attributes[$key])) {
                $attrs[$key] = $attributes[$key];
            }
        }

        foreach ($attributes as $key => $value) {
            if (true !== isset($attrs[$key])) {
                $attrs[$key] = $value;
            }
        }

        unset($attrs['escape']);

        $newCode = $code;
        foreach ($attrs as $key => $value) {
            if (true === is_string($key) && null !== $value) {
                if (true === is_array($value) || true === is_resource($value)) {
                    throw new Exception(
                        'Value at index: "' . $key . '" type: "' .
                        gettype($value) . '" cannot be rendered'
                    );
                }

                $newCode .= ' '
                    . $key
                    . '="'
                    . htmlspecialchars(
                        $value,
                        ENT_QUOTES,
                        'utf-8',
                        true
                    )
                    . '"';
            }
        }

        return $newCode;
    }
}
