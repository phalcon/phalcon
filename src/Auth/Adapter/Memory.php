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

namespace Phalcon\Auth\Adapter;

use Phalcon\Auth\Adapter\Config\MemoryAdapterConfig;
use Phalcon\Auth\Internal\Options;
use Phalcon\Contracts\Auth\AuthUser;
use Phalcon\Contracts\Encryption\Security\Security;

/**
 * In-memory adapter — useful for tests and small read-only user lists.
 *
 * @phpstan-import-type AuthUserRow from AbstractArrayAdapter
 *
 * @extends AbstractArrayAdapter<MemoryAdapterConfig>
 */
class Memory extends AbstractArrayAdapter
{
    /**
     * Map of id => user row for O(1) retrieveById lookup.
     *
     * @var array<int|string, AuthUserRow>
     */
    private array $idStore = [];

    public function __construct(Security $hasher, MemoryAdapterConfig $config)
    {
        parent::__construct($hasher, $config);

        foreach ($this->loadUsers() as $row) {
            if (isset($row['id'])) {
                $this->idStore[$row['id']] = $row;
            }
        }
    }

    public static function fromOptions(Security $hasher, array $options): static
    {
        return new static(
            $hasher,
            new MemoryAdapterConfig(
                Options::arrayOption($options, 'users', []),
                Options::stringOrNull($options, 'model')
            )
        );
    }

    /**
     * Overridden for O(1) lookup via the id index built in the constructor.
     */
    public function retrieveById(int | string $id): ?AuthUser
    {
        if (!isset($this->idStore[$id])) {
            return null;
        }

        return $this->hydrate($this->idStore[$id]);
    }

    protected function loadUsers(): array
    {
        return $this->config->getUsers();
    }
}
