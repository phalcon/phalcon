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

use Phalcon\Acl\ComponentAwareInterface;
use Phalcon\Acl\ComponentInterface;
use Phalcon\Acl\RoleAwareInterface;
use Phalcon\Acl\RoleInterface;

/**
 * Interface for Phalcon\Acl adapters
 *
 * @phpstan-type TComponent = array{string: ComponentInterface}
 * @phpstan-type TRole = array{string: RoleInterface}
 * @phpstan-type TRoleToInherit = RoleInterface|TRole|string
 */
interface AdapterInterface
{
    /**
     * Adds a component to the ACL list
     *
     * Access names can be a particular action, for instance `search`, `update`
     * `delete` etc. or a list of them.
     *
     * @param ComponentInterface|string $componentObject
     * @param TComponent|string         $accessList
     *
     * @return bool
     */
    public function addComponent(
        ComponentInterface | string $componentObject,
        array | string $accessList
    ): bool;

    /**
     * Adds access to components
     *
     * @param string            $componentName
     * @param TComponent|string $accessList
     *
     * @return bool
     */
    public function addComponentAccess(
        string $componentName,
        array | string $accessList
    ): bool;

    /**
     * Add a role which inherits from an existing role
     *
     * @param string         $roleName
     * @param TRoleToInherit $roleToInherit
     *
     * @return bool
     */
    public function addInherit(
        string $roleName,
        RoleInterface | array | string $roleToInherit
    ): bool;

    /**
     * Adds a role to the ACL list. The second parameter lets to inherit access
     * from an existing role
     *
     * @param RoleInterface|string $roleObject
     * @param TRoleToInherit|null  $accessInherits
     *
     * @return bool
     */
    public function addRole(
        RoleInterface | string $roleObject,
        RoleInterface | array | string | null $accessInherits = null
    ): bool;

    /**
     * Allow access to a role on a component. You can use `*` as wildcard
     *
     * @param string               $roleName
     * @param string               $componentName
     * @param array<string>|string $access
     * @param callable|null        $function
     *
     * @return void
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
     * @param string               $roleName
     * @param string               $componentName
     * @param array<string>|string $access
     * @param callable|null        $function
     *
     * @return void
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
     * @param string               $componentName
     * @param array<string>|string $accessList
     */
    public function dropComponentAccess(
        string $componentName,
        array | string $accessList
    ): void;

    /**
     * Returns the access which the list is checking if a role can access it
     *
     * @return string|null
     */
    public function getActiveAccess(): string | null;

    /**
     * Returns the component which the list is checking if some role can access
     * it
     *
     * @return string|null
     */
    public function getActiveComponent(): string | null;

    /**
     * Returns the role which the list is checking if 's allowed to certain
     * component/access
     *
     * @return string|null
     */
    public function getActiveRole(): string | null;

    /**
     * Return an array with every component registered in the list
     *
     * @return array<string, ComponentInterface>
     */
    public function getComponents(): array;

    /**
     * Returns the default action
     *
     * @return int
     */
    public function getDefaultAction(): int;

    /**
     * Returns the inherited roles for a passed role name. If no role name
     * has been specified it will return the whole array. If the role has not
     * been found it returns an empty array
     *
     * @param string $roleName
     *
     * @return array<int|string, string|array<int, string>>
     */
    public function getInheritedRoles(string $roleName = ""): array;

    /**
     * Returns the default ACL access level for no arguments provided in
     * `isAllowed` action if a `function` (callable) exists for `accessKey`
     *
     * @return int
     */
    public function getNoArgumentsDefaultAction(): int;

    /**
     * Return an array with every role registered in the list
     *
     * @return array<string, RoleInterface>
     */
    public function getRoles(): array;

    /**
     * Check whether a role is allowed to access an action from a component
     *
     * @param RoleAwareInterface|RoleInterface           $roleName
     * @param ComponentAwareInterface|ComponentInterface $componentName
     * @param string                                     $access
     * @param array<int|string, mixed>                   $parameters
     *
     * @return bool
     */
    public function isAllowed(
        RoleAwareInterface | RoleInterface $roleName,
        ComponentAwareInterface | ComponentInterface $componentName,
        string $access,
        array $parameters = []
    ): bool;

    /**
     * Check whether a component exists in the components list
     *
     * @param string $componentName
     *
     * @return bool
     */
    public function isComponent(string $componentName): bool;

    /**
     * Check whether role exist in the roles list
     *
     * @param string $roleName
     *
     * @return bool
     */
    public function isRole(string $roleName): bool;

    /**
     * Sets the default access level
     * (Phalcon\Acl\Enum::ALLOW or Phalcon\Acl\Enum::DENY)
     *
     * @param int $defaultAccess
     */
    public function setDefaultAction(int $defaultAccess): void;

    /**
     * Sets the default access level (Phalcon\Acl\Enum::ALLOW or
     * Phalcon\Acl\Enum::DENY) for no arguments provided in isAllowed action if
     * there exists func for accessKey
     *
     * @param int $defaultAccess
     */
    public function setNoArgumentsDefaultAction(int $defaultAccess): void;
}
