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

namespace Phalcon\Acl\Adapter;

use Phalcon\Acl\ComponentInterface;
use Phalcon\Acl\Enum;
use Phalcon\Acl\RoleInterface;
use Phalcon\Contracts\Acl\Adapter\Persistable;
use Phalcon\Storage\Adapter\AdapterInterface as StorageInterface;

use function is_array;

/**
 * ACL adapter that persists its policy to any Phalcon\Storage backend
 * (Redis, Apcu, Stream, Memcached, …) as a whole-policy snapshot.
 *
 * Coarse granularity: the entire policy is loaded into memory and all
 * decisions are computed in memory (inherited from Memory). The backend is a
 * blob sink — it knows nothing about ACL structure.
 *
 * @see Persistable for the closure-persistence caveat.
 *
 * @phpstan-type TSnapshot array{
 *     access?: array<string, int>,
 *     accessList?: array<string, bool>,
 *     components?: array<string, ComponentInterface>,
 *     componentsNames?: array<string, bool>,
 *     roles?: array<string, RoleInterface>,
 *     roleInherits?: array<string, array<int, string>>,
 *     defaultAccess?: int,
 *     noArgumentsDefaultAction?: int
 * }
 */
class Storage extends Memory implements Persistable
{
    /**
     * @param StorageInterface $storage
     * @param string           $key
     */
    public function __construct(
        protected StorageInterface $storage,
        protected string $key = 'acl-data'
    ) {
        parent::__construct();

        $this->load();
    }

    /**
     * @return bool
     */
    public function load(): bool
    {
        $data = $this->storage->get($this->key);
        if (!is_array($data)) {
            return false;
        }

        /** @var TSnapshot $data */
        $this->access                   = $data['access'] ?? [];
        $this->accessList               = $data['accessList'] ?? ["*!*" => true];
        $this->components               = $data['components'] ?? [];
        $this->componentsNames          = $data['componentsNames'] ?? ["*" => true];
        $this->roles                    = $data['roles'] ?? [];
        $this->roleInherits             = $data['roleInherits'] ?? [];
        $this->defaultAccess            = $data['defaultAccess'] ?? Enum::DENY;
        $this->noArgumentsDefaultAction = $data['noArgumentsDefaultAction'] ?? Enum::DENY;

        return true;
    }

    /**
     * Persists the policy snapshot. Callable rules (`functions`) are excluded —
     * closures are not serializable; the static rule set in `access` survives.
     *
     * @return bool
     */
    public function save(): bool
    {
        return $this->storage->set(
            $this->key,
            [
                'access'                   => $this->access ?? [],
                'accessList'               => $this->accessList,
                'components'               => $this->components ?? [],
                'componentsNames'          => $this->componentsNames,
                'roles'                    => $this->roles ?? [],
                'roleInherits'             => $this->roleInherits ?? [],
                'defaultAccess'            => $this->defaultAccess,
                'noArgumentsDefaultAction' => $this->noArgumentsDefaultAction,
            ]
        );
    }
}
