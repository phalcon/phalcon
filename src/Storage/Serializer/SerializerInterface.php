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

use Serializable;

/**
 * Interface SerializerInterface
 *
 * @package Phalcon\Storage\Serializer
 */
interface SerializerInterface extends Serializable
{
    /**
     * @var mixed
     */
    public function getData();

    /**
     * @param mixed $data
     */
    public function setData($data): void;
}
