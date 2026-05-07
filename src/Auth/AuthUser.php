<?php

/**
 * This file is part of the Phalcon Framework.
 *
 * (c) Phalcon Team <team@phalcon.io>
 *
 * For the full copyright and license information, please view the LICENSE.txt
 * file that was distributed with this source code.
 *
 * Implementation of this file has been influenced by sinbadxiii/cphalcon-auth
 * @link    https://github.com/sinbadxiii/cphalcon-auth
 */

declare(strict_types=1);

namespace Phalcon\Auth;

use Phalcon\Contracts\Auth\AuthUser as AuthUserContract;

/**
 * Lightweight value object returned by array-backed adapters (Memory, Stream)
 * when no application model class is configured.
 */
class AuthUser implements AuthUserContract
{
    /**
     * @var array<string, mixed>
     */
    protected array $data;

    /**
     * @param array<string, mixed> $data
     *
     * @throws Exception when $data does not contain a scalar 'id' key.
     */
    public function __construct(array $data)
    {
        if (!isset($data['id']) || (!is_int($data['id']) && !is_string($data['id']))) {
            throw Exception::dataMustContainIdKey();
        }

        $this->data = $data;
    }

    public function getAuthIdentifier(): int | string
    {
        /** @var int|string $id (validated in constructor) */
        $id = $this->data['id'];

        return $id;
    }

    public function getAuthPassword(): string
    {
        $password = $this->data['password'] ?? null;

        return is_string($password) ? $password : '';
    }

    /**
     * Returns the underlying data array.
     *
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return $this->data;
    }
}
