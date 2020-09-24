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
use function restore_error_handler;
use function set_error_handler;

use const E_WARNING;

/**
 * Class AbstractSerializer
 *
 * @property mixed|null $data
 * @property int        $errorType
 */
abstract class AbstractSerializer implements SerializerInterface
{
    /**
     * @var mixed
     */
    protected $data = null;

    /**
     * @var int
     */
    protected int $errorType = E_WARNING;

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
     * Serializes data
     *
     * @return string|null
     */
    public function serialize()
    {
        if (true !== $this->isSerializable($this->data)) {
            return $this->data;
        }

        $serialized = $this->internalSerialize($this->data);
        if (false === $serialized) {
            $serialized = '';
        }

        return $serialized;
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
     * Unserializes data
     *
     * @param string $data
     */
    public function unserialize($data): void
    {
        $warning = false;
        set_error_handler(
            function () use (&$warning) {
                $warning = true;
            },
            $this->errorType
        );

        $this->data = $this->internalUnserlialize($data);

        restore_error_handler();

        if ($warning) {
            $this->data = null;
        }
    }

    abstract protected function internalSerialize($data);
    abstract protected function internalUnserlialize($data);

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
