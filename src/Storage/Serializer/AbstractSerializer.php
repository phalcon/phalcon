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

use Phalcon\Contracts\Storage\StorageTypes;

use function is_array;
use function is_bool;
use function is_numeric;

/**
 * @property mixed $data
 * @property bool  $isSuccess
 *
 * @phpstan-import-type storage_serializer_data from StorageTypes
 */
abstract class AbstractSerializer implements SerializerInterface
{
    /**
     * @var mixed
     */
    protected $data           = null;
    protected bool $isSuccess = true;

    /**
     * AbstractSerializer constructor.
     *
     * @param mixed $data
     */
    public function __construct($data = null)
    {
        $this->setData($data);
    }

    /**
     * Serialize data
     *
     * @return array
     *
     * @phpstan-return storage_serializer_data
     */
    public function __serialize(): array
    {
        if (true === is_array($this->data)) {
            return $this->data;
        }

        return [];
    }

    /**
     * Unserialize data
     *
     * @phpstan-param storage_serializer_data $data
     */
    public function __unserialize(array $data): void
    {
        $this->data = $data;
    }

    public function getData(): mixed
    {
        return $this->data;
    }

    /**
     * Returns `true` if the serialize/unserialize operation was successful;
     * `false` otherwise
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
     * @phpstan-assert-if-false bool|float|int|numeric-string|null $data
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
