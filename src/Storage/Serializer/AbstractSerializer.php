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
 * @property mixed|null $data
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
     * @param null|mixed $data
     */
    public function __construct($data = null)
    {
        $this->setData($data);
    }

    /**
     * Returns the internal array
     *
     * @return mixed|null
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * Sets the data
     *
     * @param mixed $data
     */
    public function setData($data): void
    {
        $this->data = $data;
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
        return !(empty($data) || is_bool($data) || is_numeric($data));
    }
}
