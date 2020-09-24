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
use function igbinary_unserialize;
use function restore_error_handler;
use function set_error_handler;

use const E_WARNING;

/**
 * Class Igbinary
 *
 * @package Phalcon\Storage\Serializer
 */
class Igbinary extends AbstractSerializer
{
    /**
     * @param mixed $data
     *
     * @return mixed
     */
    protected function internalSerialize($data)
    {
        return igbinary_serialize($data);
    }

    /**
     * @param mixed $data
     *
     * @return mixed
     */
    protected function internalUnserlialize($data)
    {
        return igbinary_unserialize($data);
    }
}
