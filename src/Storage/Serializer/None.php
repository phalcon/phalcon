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

namespace Phalcon\Storage\Serializer;

/**
 * Class None
 *
 * @package Phalcon\Storage\Serializer
 */
class None extends AbstractSerializer
{
    /**
     * Serializes data
     *
     * @return string
     */
    public function serialize()
    {
        return $this->internalSerialize($this->data);
    }

    /**
     * Unserializes data
     *
     * @param string $data
     */
    public function unserialize($data): void
    {
        $this->data = $this->internalUnserlialize($data);
    }

    /**
     * @param mixed $data
     *
     * @return mixed
     */
    protected function internalSerialize($data)
    {
        return $data;
    }

    /**
     * @param mixed $data
     *
     * @return mixed
     */
    protected function internalUnserlialize($data)
    {
        return $data;
    }
}
