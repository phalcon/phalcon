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
use Phalcon\Storage\Traits\StorageErrorHandlerTrait;

use function is_string;
use function serialize;

use const E_NOTICE;

class Php extends AbstractSerializer
{
    use StorageErrorHandlerTrait;

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

        return serialize($this->data);
    }

    /**
     * Unserializes data
     *
     * @param string $data
     */
    public function unserialize($data)
    {
        $this->processSerializable($data);
        $this->processNotSerializable($data);
    }

    /**
     * @param mixed $data
     */
    private function processSerializable($data): void
    {
        if (true === $this->isSerializable($data)) {
            if (true !== is_string($data)) {
                throw new InvalidArgumentException(
                    'Data for the unserializer must of type string'
                );
            }

            $this->data = $this->callMethodWithError(
                'unserialize',
                E_NOTICE,
                $data
            );
        }
    }

    /**
     * @param mixed $data
     */
    private function processNotSerializable($data): void
    {
        if (true !== $this->isSerializable($data)) {
            $this->data = $data;
        }
    }
}
