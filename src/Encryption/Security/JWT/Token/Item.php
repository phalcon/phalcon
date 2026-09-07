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

namespace Phalcon\Encryption\Security\JWT\Token;

use Phalcon\Contracts\Encryption\EncryptionTypes;

/**
 * Storage class for a Token Item
 *
 * @phpstan-import-type encryption_jwt_payload from EncryptionTypes
 */
class Item extends AbstractItem
{
    /**
     * Item constructor.
     *
     * @phpstan-param encryption_jwt_payload $payload
     */
    public function __construct(array $payload, string $encoded)
    {
        $this->data['encoded'] = $encoded;
        $this->data['payload'] = $payload;
    }

    /**
     * @return mixed|null
     */
    public function get(string $name, mixed $defaultValue = null): mixed
    {
        if (true !== $this->has($name)) {
            return $defaultValue;
        }

        /** @phpstan-var encryption_jwt_payload $payload */
        $payload = $this->data['payload'];

        return $payload[$name];
    }

    /**
     * @phpstan-return encryption_jwt_payload
     */
    public function getPayload(): array
    {
        /** @phpstan-var encryption_jwt_payload $payload */
        $payload = $this->data['payload'];

        return $payload;
    }

    public function has(string $name): bool
    {
        /** @phpstan-var encryption_jwt_payload $payload */
        $payload = $this->data['payload'];

        return isset($payload[$name]);
    }
}
