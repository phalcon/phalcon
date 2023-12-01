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

/**
 * Storage class for a Token Item
 */
class Item extends AbstractItem
{
    /**
     * Item constructor.
     *
     * @param array  $payload
     * @param string $encoded
     */
    public function __construct(array $payload, string $encoded)
    {
        $this->data['encoded'] = $encoded;
        $this->data['payload'] = $payload;
    }

    /**
     * @param string     $name
     * @param mixed|null $defaultValue
     *
     * @return mixed|null
     */
    public function get(string $name, mixed $defaultValue = null)
    {
        if (true !== $this->has($name)) {
            return $default;
        }

        return $this->data['payload'][$name];
    }

    /**
     * @return array
     */
    public function getPayload(): array
    {
        return $this->data['payload'];
    }

    /**
     * @param string $name
     *
     * @return bool
     */
    public function has(string $name): bool
    {
        return isset($this->data['payload'][$name]);
    }
}
