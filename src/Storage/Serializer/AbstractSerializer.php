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

use function is_bool;
use function is_numeric;

/**
 * Class AbstractSerializer
 *
 * @package Phalcon\Storage\Serializer
 *
 * @property mixed $data
 */
abstract class AbstractSerializer implements SerializerInterface
{
    /**
     * @var mixed
     */
    protected $data = null;

    /**
     * AbstractSerializer constructor.
     *
     * @param null $data
     */
    public function __construct($data = null)
    {
        $this->setData($data);
    }

    /**
     * If this returns true, then the data returns back as is
     *
     * @param mixed $data
     *
     * @return bool
     */
    protected function isSerializable($data): bool
    {
        return !(
            true === empty($data) ||
            true === is_bool($data) ||
            true === is_numeric($data)
        );
    }

    /**
     * @return mixed
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * @param mixed $data
     */
    public function setData($data): void
    {
        $this->data = $data;
    }
}
