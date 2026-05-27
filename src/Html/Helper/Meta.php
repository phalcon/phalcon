<?php

/**
 * This file is part of the Phalcon Framework.
 *
 * (c) Phalcon Team <team@phalcon.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * Implementation of this file has been influenced by AuraPHP
 * @link    https://github.com/auraphp/Aura.Html
 * @license https://github.com/auraphp/Aura.Html/blob/2.x/LICENSE
 */

declare(strict_types=1);

namespace Phalcon\Html\Helper;

/**
 * Class Meta
 */
class Meta extends AbstractSeries
{
    /**
     * Add an element to the list
     *
     * @param array $attributes
     * @param int   $pos
     *
     * @return Meta
     */
    public function add(array $attributes = [], int $pos = -1): static
    {
        $this->pushOrPlace(
            [
                'renderTag',
                [
                    $this->getTag(),
                    $attributes,
                ],
                $this->indent(),
            ],
            $pos
        );

        return $this;
    }

    /**
     * @param string $httpEquiv
     * @param string $content
     * @param int    $pos
     *
     * @return Meta
     */
    public function addHttp(string $httpEquiv, string $content, int $pos = -1): static
    {
        return $this->addElement('http-equiv', $httpEquiv, $content, $pos);
    }

    /**
     * @param string $name
     * @param string $content
     * @param int    $pos
     *
     * @return Meta
     */
    public function addName(string $name, string $content, int $pos = -1): static
    {
        $this->addElement('name', $name, $content, $pos);

        return $this;
    }

    /**
     * @param string $name
     * @param string $content
     * @param int    $pos
     *
     * @return Meta
     */
    public function addProperty(string $name, string $content, int $pos = -1): static
    {
        $this->addElement('property', $name, $content, $pos);

        return $this;
    }

    /**
     * @return string
     */
    protected function getTag(): string
    {
        return 'meta';
    }

    /**
     * @param string $element
     * @param string $value
     * @param string $content
     * @param int    $pos
     *
     * @return Meta
     */
    private function addElement(
        string $element,
        string $value,
        string $content,
        int $pos = -1
    ): static {
        $attributes = [
            $element  => $value,
            'content' => $content,
        ];

        return $this->add($attributes, $pos);
    }
}
