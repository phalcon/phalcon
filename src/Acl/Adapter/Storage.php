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

use Phalcon\Acl\Component;
use Phalcon\Acl\Enum;
use Phalcon\Acl\Exceptions\InvalidSnapshot;
use Phalcon\Acl\Role;
use Phalcon\Contracts\Acl\AclTypes;
use Phalcon\Contracts\Acl\Adapter\Persistable;
use Phalcon\Storage\Adapter\AdapterInterface as StorageInterface;
use Throwable;

use function array_keys;
use function get_object_vars;
use function is_array;
use function is_object;

/**
 * ACL adapter that persists its policy to any Phalcon\Storage backend
 * (Redis, Apcu, Stream, Memcached, ...) as a whole-policy snapshot.
 *
 * The snapshot is a versioned, scalar-only structure: roles and components are
 * stored as `name => description` maps and rebuilt into objects on load, so the
 * snapshot round-trips through any serializer (php, json, igbinary, msgpack).
 *
 * Callable (closure) rules are not serializable. Any access key backed by a
 * closure is persisted as DENY, so a reloaded policy fails closed until the
 * closure is re-registered after load().
 *
 * Single-writer contract: mutations are in-memory until save() is called, and
 * save() writes the whole snapshot (last-write-wins, no atomic check-and-set).
 * Use external locking when multiple processes write the same key.
 *
 * @see Persistable
 *
 * @phpstan-import-type acl_snapshot from AclTypes
 */
class Storage extends Memory implements Persistable
{
    public const SNAPSHOT_VERSION = 1;

    public function __construct(
        protected StorageInterface $storage,
        protected string $key = 'acl-data'
    ) {

        $this->load();
    }

    /**
     * Loads the policy snapshot from the backing store, replacing current
     * in-memory state. Returns false when no compatible snapshot exists; throws
     * Phalcon\Acl\Exceptions\InvalidSnapshot on an incompatible version or a
     * malformed structure.
     */
    public function load(): bool
    {
        $data = $this->storage->get($this->key);

        if (is_object($data)) {
            $data = $this->normalizeToArray($data);
        }

        if (!is_array($data)) {
            return false;
        }

        if (!isset($data['version'])) {
            return false;
        }

        /** @var int|string $version */
        $version = $data['version'];

        if ($version != self::SNAPSHOT_VERSION) {
            throw new InvalidSnapshot(
                "Incompatible ACL snapshot version '" . $version
                . "'; expected '" . self::SNAPSHOT_VERSION . "'"
            );
        }

        if (
            !is_array($data['access'] ?? null)
            || !is_array($data['accessList'] ?? null)
            || !is_array($data['components'] ?? null)
            || !is_array($data['componentsNames'] ?? null)
            || !is_array($data['roles'] ?? null)
            || !is_array($data['roleInherits'] ?? null)
        ) {
            throw new InvalidSnapshot('Malformed ACL snapshot structure');
        }

        /**
         * Validate everything before any state is replaced, so a bad
         * snapshot never leaves the adapter half loaded.
         */
        $defaultAccess            = $data['defaultAccess'] ?? Enum::DENY;
        $noArgumentsDefaultAction = $data['noArgumentsDefaultAction'] ?? Enum::DENY;

        if (
            ($defaultAccess !== Enum::ALLOW && $defaultAccess !== Enum::DENY)
            || ($noArgumentsDefaultAction !== Enum::ALLOW && $noArgumentsDefaultAction !== Enum::DENY)
        ) {
            throw new InvalidSnapshot('Malformed ACL snapshot default action');
        }

        foreach ($data['roleInherits'] as $inherits) {
            if (!is_array($inherits)) {
                throw new InvalidSnapshot('Malformed ACL snapshot role inheritance');
            }

            foreach ($inherits as $inherit) {
                if (!is_string($inherit) && !is_int($inherit)) {
                    throw new InvalidSnapshot('Malformed ACL snapshot role inheritance');
                }
            }
        }

        /** @var acl_snapshot $data */
        try {
            $roles = [];
            foreach ($data['roles'] as $name => $description) {
                $roles[$name] = new Role((string) $name, $description);
            }

            $components = [];
            foreach ($data['components'] as $name => $description) {
                $components[$name] = new Component((string) $name, $description);
            }
        } catch (Throwable $e) {
            throw new InvalidSnapshot(
                'Malformed ACL snapshot element: ' . $e->getMessage()
            );
        }

        $this->access                   = $data['access'];
        $this->accessList               = $data['accessList'];
        $this->components               = $components;
        $this->componentsNames          = $data['componentsNames'];
        $this->roles                    = $roles;
        $this->roleInherits             = $data['roleInherits'];
        $this->defaultAccess            = $defaultAccess;
        $this->noArgumentsDefaultAction = $noArgumentsDefaultAction;

        return true;
    }

    /**
     * Persists the policy snapshot. Closure-backed access keys are written as
     * DENY (fail closed); roles/components are written as scalar name =>
     * description maps for serializer independence.
     */
    public function save(): bool
    {
        $access = $this->access;
        foreach (array_keys($this->functions) as $accessKey) {
            $access[$accessKey] = Enum::DENY;
        }

        $components = [];
        foreach ($this->components as $componentName => $componentObject) {
            $components[$componentName] = $componentObject->getDescription();
        }

        $roles = [];
        foreach ($this->roles as $roleName => $roleObject) {
            $roles[$roleName] = $roleObject->getDescription();
        }

        return $this->storage->set(
            $this->key,
            [
                'version'                  => self::SNAPSHOT_VERSION,
                'access'                   => $access,
                'accessList'               => $this->accessList,
                'components'               => $components,
                'componentsNames'          => $this->componentsNames,
                'roles'                    => $roles,
                'roleInherits'             => $this->roleInherits,
                'defaultAccess'            => $this->defaultAccess,
                'noArgumentsDefaultAction' => $this->noArgumentsDefaultAction,
            ]
        );
    }

    /**
     * Recursively converts stdClass into nested arrays so a snapshot stored
     * through an object-decoding serializer (e.g. JSON) is read back the same
     * way as the array-decoding serializers (php, igbinary, msgpack). A
     * snapshot is at most three levels deep; a deeper (or cyclic) graph is
     * rejected.
     */
    private function normalizeToArray(mixed $value, int $depth = 0): mixed
    {
        if ($depth > 4) {
            throw new InvalidSnapshot('ACL snapshot nesting is too deep');
        }

        if (is_object($value)) {
            $value = get_object_vars($value);
        }

        if (!is_array($value)) {
            return $value;
        }

        $result = [];
        foreach ($value as $key => $item) {
            $result[$key] = $this->normalizeToArray($item, $depth + 1);
        }

        return $result;
    }
}
