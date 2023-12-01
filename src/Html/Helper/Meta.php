<?php

/**
 * This file is part of the Phalcon Framework.
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Phalcon\Html\Helper;

/**
 * Class Meta
 *
 * @package Phalcon\Html\Helper
 */
class Meta extends AbstractSeries
{
    /**
     * Add an element to the list
     *
     * @param array $attributes
     *
     * @return Meta
     */
    public function add(array $attributes = []): Meta
    {
        $this->store[] = [
            'renderTag',
            [
                $this->getTag(),
                $attributes,
            ],
            $this->indent(),
        ];

        return $this;
    }

    /**
     * @param string $httpEquiv
     * @param string $content
     *
     * @return Meta
     */
    public function addHttp(string $httpEquiv, string $content): Meta
    {
        return $this->addElement('http-equiv', $httpEquiv, $content);
    }

    /**
     * @param string $name
     * @param string $content
     *
     * @return Meta
     */
    public function addName(string $name, string $content): Meta
    {
        $this->addElement('name', $name, $content);

        return $this;
    }

    /**
     * @param string $name
     * @param string $content
     *
     * @return Meta
     */
    public function addProperty(string $name, string $content): Meta
    {
        $this->addElement('property', $name, $content);

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
     *
     * @return Meta
     */
    private function addElement(
        string $element,
        string $value,
        string $content
    ): Meta {
        $attributes = [
            $element  => $value,
            'content' => $content,
        ];

        return $this->add($attributes);
    }
}
