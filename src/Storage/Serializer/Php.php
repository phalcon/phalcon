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

use InvalidArgumentException;
use function is_string;
use function restore_error_handler;
use function serialize;
use function set_error_handler;
use function unserialize;
use const E_NOTICE;

/**
 * Class Php
 *
 * @package Phalcon\Storage\Serializer
 */
class Php extends AbstractSerializer
{
    /**
     * Serializes data
     *
     * @return string
     */
    public function serialize()
    {
        if (true !== $this->isSerializable($this->data)) {
            return $this->data;
        }

        return serialize($this->data);
    }

    /**
     * Unserializes data
     *
     * @param mixed $data
     */
    public function unserialize($data): void
    {
        if (false === $this->isSerializable($data)) {
            $this->data = $data;
        } else {
            if (!is_string($data)) {
                throw new InvalidArgumentException(
                    'Data for the unserializer must of type string'
                );
            }

            $warning = false;
            set_error_handler(
                function () use (&$warning) {
                    $warning = true;
                },
                E_NOTICE
            );

            $this->data = unserialize($data);

            restore_error_handler();

            if ($warning) {
                $this->data = null;
            }
        }
    }
}
