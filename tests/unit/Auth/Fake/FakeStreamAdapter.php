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

namespace Phalcon\Tests\Unit\Auth\Fake;

use Phalcon\Auth\Adapter\Config\StreamAdapterConfig;
use Phalcon\Auth\Adapter\Stream;
use Phalcon\Contracts\Encryption\Security\Security;

final class FakeStreamAdapter extends Stream
{
    /**
     * @var array<int, array<string, mixed>>
     */
    private array $injectedUsers = [];

    public function __construct(Security $hasher)
    {
        parent::__construct($hasher, new StreamAdapterConfig('unused.json'));
    }

    public function setUsers(array $users): void
    {
        $this->injectedUsers = $users;
    }

    protected function loadUsers(): array
    {
        return $this->injectedUsers;
    }
}
