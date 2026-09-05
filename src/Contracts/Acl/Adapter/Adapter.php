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

namespace Phalcon\Contracts\Acl\Adapter;

use Phalcon\Acl\ComponentInterface;
use Phalcon\Acl\RoleInterface;
use Phalcon\Contracts\Acl\AclTypes;

/**
 * Canonical contract for Phalcon\Acl adapters
 *
 * @phpstan-import-type acl_access_list from AclTypes
 * @phpstan-import-type acl_component_name from AclTypes
 * @phpstan-import-type acl_components from AclTypes
 * @phpstan-import-type acl_role_name from AclTypes
 * @phpstan-import-type acl_role_to_inherit from AclTypes
 * @phpstan-import-type acl_roles from AclTypes
 */
interface Adapter
{
    /**
     * Adds a component to the ACL list
     *
     * Access names can be a particular action, for instance `search`, `update`
     * `delete` etc. or a list of them.
     *
     * @param acl_access_list $accessList
     */
    public function addComponent(
        ComponentInterface | string $componentObject,
        array | string $accessList
    ): bool;

    /**
     * Adds access to components
     *
     * @phpstan-param acl_access_list $accessList
     */
    public function addComponentAccess(
        string $componentName,
        mixed $accessList
    ): bool;

    /**
     * Add a role which inherits from an existing role
     *
     * @param acl_role_to_inherit $roleToInherit
     */
    public function addInherit(
        string $roleName,
        array | RoleInterface | string $roleToInherit
    ): bool;

    /**
     * Adds a role to the ACL list. The second parameter lets to inherit access
     * from an existing role
     *
     * @param acl_role_to_inherit|null $accessInherits
     *
     * @phpstan-param RoleInterface|string $roleObject
     */
    public function addRole(
        mixed $roleObject,
        array | RoleInterface | string | null $accessInherits = null
    ): bool;

    /**
     * Allow access to a role on a component. You can use `*` as wildcard
     *
     * @param acl_access_list $access
     */
    public function allow(
        string $roleName,
        string $componentName,
        array | string $access,
        callable | null $function = null
    ): void;

    /**
     * Deny access to a role on a component. You can use `*` as wildcard
     *
     * @param acl_access_list $access
     */
    public function deny(
        string $roleName,
        string $componentName,
        array | string $access,
        callable | null $function = null
    ): void;

    /**
     * Removes access from a component
     *
     * @param acl_access_list $accessList
     */
    public function dropComponentAccess(
        string $componentName,
        array | string $accessList
    ): void;

    /**
     * Returns the access which the list is checking if a role can access it
     */
    public function getActiveAccess(): string | null;

    /**
     * Returns the component which the list is checking if some role can access
     * it
     */
    public function getActiveComponent(): string | null;

    /**
     * Returns the role which the list is checking if it's allowed to certain
     * component/access
     */
    public function getActiveRole(): string | null;

    /**
     * Return an array with every component registered in the list
     *
     * @return acl_components
     */
    public function getComponents(): array | null;

    /**
     * Returns the default action
     */
    public function getDefaultAction(): int;

    /**
     * Returns the inherited roles for a passed role name. If no role name
     * has been specified it will return the whole array. If the role has not
     * been found it returns an empty array
     *
     * @return array<int|string, array<int, string>|string>
     */
    public function getInheritedRoles(string $roleName = ''): array | null;

    /**
     * Returns the default ACL access level for no arguments provided in
     * `isAllowed` action if a `function` (callable) exists for `accessKey`
     */
    public function getNoArgumentsDefaultAction(): int;

    /**
     * Return an array with every role registered in the list
     *
     * @return acl_roles
     */
    public function getRoles(): array | null;

    /**
     * Check whether a role is allowed to access an action from a component
     *
     * @param array<int|string, mixed> $parameters
     *
     * @phpstan-param acl_role_name      $roleName
     * @phpstan-param acl_component_name $componentName
     */
    public function isAllowed(
        mixed $roleName,
        mixed $componentName,
        string $access,
        array | null $parameters = null
    ): bool;

    /**
     * Check whether a component exists in the components list
     */
    public function isComponent(string $componentName): bool;

    /**
     * Check whether role exist in the roles list
     */
    public function isRole(string $roleName): bool;

    /**
     * Sets the default access level
     * (Phalcon\Acl\Enum::ALLOW or Phalcon\Acl\Enum::DENY)
     */
    public function setDefaultAction(int $defaultAccess): void;

    /**
     * Sets the default access level (Phalcon\Acl\Enum::ALLOW or
     * Phalcon\Acl\Enum::DENY) for no arguments provided in isAllowed action if
     * there exists func for accessKey
     */
    public function setNoArgumentsDefaultAction(int $defaultAccess): void;
}
