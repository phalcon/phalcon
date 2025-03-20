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

use function igbinary_serialize;
use function restore_error_handler;
use function set_error_handler;

use const E_WARNING;

class Igbinary extends AbstractSerializer
{
    /**
     * Serializes data
     *
     * @return string
     */
    public function serialize(): mixed
    {
        if (true !== $this->isSerializable($this->data)) {
            return $this->data;
        }

        $result = $this->phpIgbinarySerialize($this->data);

        if (null === $result) {
            $this->isSuccess = false;
            $result          = "";
        }

        return $result;
    }

    /**
     * Unserializes data
     *
     * @param string $data
     *
     * @return void
     */
    public function unserialize(mixed $data): void
    {
        if (true !== $this->isSerializable($data)) {
            $this->data = $data;
        } else {
            $warning = false;
            set_error_handler(
                function () use (&$warning) {
                    $warning = true;
                },
                E_WARNING
            );

            $result = $this->doUnserialize($data);

            restore_error_handler();

            if (true === $warning || false === $result) {
                $this->isSuccess = false;
                $result          = "";
            }

            $this->data = $result;
        }
    }

    /**
     * Wrapper for `igbinary_unserialize`
     *
     * @param string $value
     *
     * @return mixed|false
     */
    protected function doUnserialize($value)
    {
        return igbinary_unserialize($value);
    }

    /**
     * Wrapper for `igbinary_serialize`
     *
     * @param mixed $value
     *
     * @return string|null
     */
    protected function phpIgbinarySerialize($value): string | null
    {
        return igbinary_serialize($value);
    }
}
