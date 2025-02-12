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
 * @property mixed $data
 * @property bool  $isSuccess
 */
abstract class AbstractSerializer implements SerializerInterface
{
    /**
     * @var mixed
     */
    protected $data = null;

    /**
     * @var bool
     */
    protected bool $isSuccess = true;

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
     * @return mixed
     */
    public function getData(): mixed
    {
        return $this->data;
    }

    /**
     * Returns `true` if the serialize/unserialize operation was successful;
     * `false` otherwise
     *
     * @return bool
     */
    public function isSuccess(): bool
    {
        return $this->isSuccess;
    }

    /**
     * @param mixed $data
     */
    public function setData($data): void
    {
        $this->data = $data;
    }

    /**
     * If this returns true, then the data is returned as is
     *
     * @param mixed $data
     *
     * @return bool
     */
    protected function isSerializable($data): bool
    {
        return !(
            null === $data ||
            is_bool($data) ||
            is_numeric($data)
        );
    }
}
