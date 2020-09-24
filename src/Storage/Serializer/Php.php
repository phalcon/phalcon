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
use function serialize;
use function unserialize;

use const E_NOTICE;

/**
 * Class Php
 *
 * @package Phalcon\Storage\Serializer
 */
class Php extends AbstractSerializer
{
    protected int $errorType = E_NOTICE;

    /**
     * Unserializes data
     *
     * @param mixed $data
     */
    public function unserialize($data): void
    {
        if (true !== is_string($data)) {
            throw new InvalidArgumentException(
                'Data for the unserializer must of type string'
            );
        }

        parent::unserialize($data);
    }

    /**
     * @param mixed $data
     *
     * @return string
     */
    protected function internalSerialize($data)
    {
        return serialize($data);
    }

    /**
     * @param mixed $data
     */
    protected function internalUnserlialize($data)
    {
        return unserialize($data);
    }
}
