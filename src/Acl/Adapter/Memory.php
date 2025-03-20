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
use Phalcon\Acl\ComponentAwareInterface;
use Phalcon\Acl\ComponentInterface;
use Phalcon\Acl\Enum;
use Phalcon\Acl\Exception;
use Phalcon\Acl\Role;
use Phalcon\Acl\RoleAwareInterface;
use Phalcon\Acl\RoleInterface;
use Phalcon\Events\Exception as EventsException;
use Phalcon\Events\Traits\EventsAwareTrait;
use ReflectionClass;
use ReflectionException;
use ReflectionFunction;
use ReflectionNamedType;

use function array_intersect_key;
use function array_keys;
use function array_shift;
use function call_user_func;
use function call_user_func_array;
use function get_class;
use function in_array;
use function is_array;
use function is_callable;
use function is_object;
use function is_string;
use function key;
use function trigger_error;

use const E_USER_WARNING;

/**
 * Manages ACL lists in memory
 *
 * @phpstan-import-type TComponent from AdapterInterface
 * @phpstan-import-type TRoleToInherit from AdapterInterface
 */
class Memory extends AbstractAdapter
{
    use EventsAwareTrait;

    /**
     * Access
     *
     * @var array<string, bool>
     */
    protected array $access = [];

    /**
     * Access List
     *
     * @var array<string, bool>
     */
    protected array $accessList = [];

    /**
     * Returns latest function used to acquire access
     *
     * @var mixed
     */
    protected mixed $activeFunction;

    /**
     * Returns number of additional arguments(excluding role and resource) for
     * active function
     *
     * @var int
     */
    protected int $activeFunctionCustomArgumentsCount = 0;

    /**
     * Returns the last key used to acquire access
     *
     * @var string|null
     */
    protected string | null $activeKey = null;

    /**
     * Components
     *
     * @var array<string, ComponentInterface>
     */
    protected array $components = [];

    /**
     * Components
     *
     * @var array<string, bool>
     */
    protected array $componentsNames = [];

    /**
     * Function List
     *
     * @var array<string, callable|string>
     */
    protected array $functions = [];

    /**
     * Default action for no arguments is `deny`
     *
     * @var int
     */
    protected int $noArgumentsDefaultAction = Enum::DENY;

    /**
     * Role Inherits
     *
     * @var array<string, array<int, string>>
     */
    protected array $roleInherits = [];

    /**
     * Roles
     *
     * @var array<string, RoleInterface>
     */
    protected array $roles = [];

    /**
     * Memory constructor.
     */
    public function __construct()
    {
        $this->componentsNames = ["*" => true];
        $this->accessList      = ["*!*" => true];
    }

    /**
     * Adds a component to the ACL list
     *
     * Access names can be a particular action, for instance `search`, `update`
     * `delete` etc. or a list of them.
     *
     * Example:
     * ```php
     * // Add a component to the list allowing access to an action
     * $acl->addComponent(
     *     new Phalcon\Acl\Component("customers"),
     *     "search"
     * );
     *
     * $acl->addComponent("customers", "search");
     *
     * // Add a component  with an access list
     * $acl->addComponent(
     *     new Phalcon\Acl\Component("customers"),
     *     [
     *         "create",
     *         "search",
     *     ]
     * );
     *
     * $acl->addComponent(
     *     "customers",
     *     [
     *         "create",
     *         "search",
     *     ]
     * );
     * ```
     *
     * @param ComponentInterface|string $componentObject
     * @param TComponent|string         $accessList
     *
     * @return bool
     * @throws Exception
     */
    public function addComponent(
        ComponentInterface | string $componentObject,
        array | string $accessList
    ): bool {
        if ($componentObject instanceof ComponentInterface) {
            $component = $componentObject;
        } else {
            $component = new Component($componentObject);
        }

        $componentName = $component->getName();

        if (!isset($this->componentsNames[$componentName])) {
            $this->components[$componentName]      = $component;
            $this->componentsNames[$componentName] = true;
        }

        return $this->addComponentAccess($componentName, $accessList);
    }

    /**
     * Adds access to components
     *
     * @param string            $componentName
     * @param TComponent|string $accessList
     *
     * @return bool
     * @throws Exception
     */
    public function addComponentAccess(
        string $componentName,
        array | string $accessList
    ): bool {
        $this->checkExists($this->componentsNames, $componentName, 'Component');

        if (is_string($accessList)) {
            $accessList = [$accessList];
        }

        foreach ($accessList as $accessName) {
            $accessKey = $componentName . '!' . $accessName;
            if (!isset($this->accessList[$accessKey])) {
                $this->accessList[$accessKey] = true;
            }
        }

        return true;
    }

    /**
     * Add a role which inherits from an existing role
     *
     * ```php
     * $acl->addRole("administrator", "consultant");
     * $acl->addRole("administrator", ["consultant", "consultant2"]);
     * ```
     *
     * @param string         $roleName
     * @param TRoleToInherit $roleToInherit
     *
     * @return bool
     * @throws Exception
     */
    public function addInherit(
        string $roleName,
        RoleInterface | array | string $roleToInherit
    ): bool {
        $this->checkExists($this->roles, $roleName, 'Role', 'role list');

        if (!isset($this->roleInherits[$roleName])) {
            $this->roleInherits[$roleName] = [];
        }

        /**
         * Type conversion
         */
        $roleToInheritList = $roleToInherit;
        if (!is_array($roleToInherit)) {
            $roleToInheritList = [$roleToInherit];
        }

        /**
         * inherits
         */
        /** @var array<RoleInterface|string> $roleToInheritList */
        foreach ($roleToInheritList as $inheritRole) {
            $roleInheritName = $inheritRole;
            if ($inheritRole instanceof RoleInterface) {
                $roleInheritName = $inheritRole->getName();
            }

            /**
             * Check if the role to inherit is repeat
             */
            if (
                true === in_array(
                    $roleInheritName,
                    $this->roleInherits[$roleName]
                )
            ) {
                continue;
            }

            /**
             * Check if the role to inherit is valid
             */
            /** @var string $roleInheritName */
            if (!isset($this->roles[$roleInheritName])) {
                throw new Exception(
                    "Role '" . $roleInheritName .
                    "' (to inherit) does not exist in the role list"
                );
            }

            if ($roleName === $roleInheritName) {
                return false;
            }

            /**
             * Deep check if the role to inherit is valid
             */
            if (isset($this->roleInherits[$roleInheritName])) {
                $checkRoleToInherits = [];

                foreach ($this->roleInherits[$roleInheritName] as $usedRoleToInherit) {
                    $checkRoleToInherits[] = $usedRoleToInherit;
                }

                $usedRoleToInherits = [];

                while (!empty($checkRoleToInherits)) {
                    $checkRoleToInherit = array_shift($checkRoleToInherits);

                    if (isset($usedRoleToInherits[$checkRoleToInherit])) {
                        continue;
                    }

                    $usedRoleToInherits[$checkRoleToInherit] = true;
                    if ($roleName === $checkRoleToInherit) {
                        throw new Exception(
                            "Role '" . $roleInheritName .
                            "' (to inherit) produces an infinite loop"
                        );
                    }

                    /**
                     * Push inherited roles
                     */
                    if (isset($this->roleInherits[$checkRoleToInherit])) {
                        foreach ($this->roleInherits[$checkRoleToInherit] as $usedRoleToInherit) {
                            $checkRoleToInherits[] = $usedRoleToInherit;
                        }
                    }
                }
            }

            $this->roleInherits[$roleName][] = $roleInheritName;
        }

        return true;
    }

    /**
     * Adds a role to the ACL list. The second parameter lets to inherit access
     * from an existing role
     *
     * ```php
     * $acl->addRole(
     *     new Phalcon\Acl\Role("administrator"),
     *     "consultant"
     * );
     *
     * $acl->addRole("administrator", "consultant");
     * $acl->addRole("administrator", ["consultant", "consultant2"]);
     * ```
     *
     * @param RoleInterface|string $roleObject
     * @param TRoleToInherit|null  $accessInherits
     *
     * @return bool
     * @throws Exception
     */
    public function addRole(
        RoleInterface | string $roleObject,
        RoleInterface | array | string | null $accessInherits = null
    ): bool {
        if ($roleObject instanceof RoleInterface) {
            $role = $roleObject;
        } else {
            $role = new Role($roleObject);
        }

        $roleName = $role->getName();
        if (isset($this->roles[$roleName])) {
            return false;
        }

        $this->roles[$roleName] = $role;

        if (null !== $accessInherits) {
            return $this->addInherit($roleName, $accessInherits);
        }

        return true;
    }

    /**
     * Allow access to a role on a component. You can use `*` as wildcard
     *
     * ```php
     * // Allow access to guests to search on customers
     * $acl->allow("guests", "customers", "search");
     *
     * // Allow access to guests to search or create on customers
     * $acl->allow("guests", "customers", ["search", "create"]);
     *
     * // Allow access to any role to browse on products
     * $acl->allow("*", "products", "browse");
     *
     * // Allow access to any role to browse on any component
     * $acl->allow("*", "*", "browse");
     *
     * @param string               $roleName
     * @param string               $componentName
     * @param array<string>|string $access
     * @param callable|null        $function
     *
     * @throws Exception
     */
    public function allow(
        string $roleName,
        string $componentName,
        array | string $access,
        callable | null $function = null
    ): void {
        $rolesArray = [$roleName];
        if ('*' === $roleName) {
            $rolesArray = array_keys($this->roles);
        }

        foreach ($rolesArray as $role) {
            $this->allowOrDeny(
                $role,
                $componentName,
                $access,
                Enum::ALLOW,
                $function
            );
        }
    }

    /**
     * Deny access to a role on a component. You can use `*` as wildcard
     *
     * ```php
     * // Deny access to guests to search on customers
     * $acl->deny("guests", "customers", "search");
     *
     * // Deny access to guests to search or create on customers
     * $acl->deny("guests", "customers", ["search", "create"]);
     *
     * // Deny access to any role to browse on products
     * $acl->deny("*", "products", "browse");
     *
     * // Deny access to any role to browse on any component
     * $acl->deny("*", "*", "browse");
     * ```
     *
     * @param string               $roleName
     * @param string               $componentName
     * @param array<string>|string $access
     * @param callable|null        $function
     *
     * @throws Exception
     */
    public function deny(
        string $roleName,
        string $componentName,
        array | string $access,
        callable | null $function = null
    ): void {
        $rolesArray = [$roleName];
        if ('*' === $roleName) {
            $rolesArray = array_keys($this->roles);
        }

        foreach ($rolesArray as $role) {
            $this->allowOrDeny(
                $role,
                $componentName,
                $access,
                Enum::DENY,
                $function
            );
        }
    }

    /**
     * Removes access from a component
     *
     * @param string               $componentName
     * @param array<string>|string $accessList
     */
    public function dropComponentAccess(
        string $componentName,
        array | string $accessList
    ): void {
        $localAccess = $accessList;
        if (is_string($accessList)) {
            $localAccess = [$accessList];
        }

        /** @var array<string> $localAccess */
        foreach ($localAccess as $accessName) {
            $accessKey = $componentName . '!' . $accessName;
            if (isset($this->accessList[$accessKey])) {
                unset($this->accessList[$accessKey]);
            }
        }
    }

    /**
     * @return mixed
     */
    public function getActiveFunction(): mixed
    {
        return $this->activeFunction;
    }

    /**
     * @return int
     */
    public function getActiveFunctionCustomArgumentsCount(): int
    {
        return $this->activeFunctionCustomArgumentsCount;
    }

    /**
     * @return string|null
     */
    public function getActiveKey(): string | null
    {
        return $this->activeKey;
    }

    /**
     * Return an array with every component registered in the list
     *
     * @return array<string, ComponentInterface>
     */
    public function getComponents(): array
    {
        return $this->components;
    }

    /**
     * Returns the inherited roles for a passed role name. If no role name
     * has been specified it will return the whole array. If the role has not
     * been found it returns an empty array
     *
     * @param string $roleName
     *
     * @return array<int|string, string|array<int, string>>
     */
    public function getInheritedRoles(string $roleName = ''): array
    {
        if ('' === $roleName) {
            return $this->roleInherits;
        }

        return $this->roleInherits[$roleName] ?? [];
    }

    /**
     * Returns the default ACL access level for no arguments provided in
     * `isAllowed` action if a `function` (callable) exists for `accessKey`
     *
     * @return int
     */
    public function getNoArgumentsDefaultAction(): int
    {
        return $this->noArgumentsDefaultAction;
    }

    /**
     * Return an array with every role registered in the list
     *
     * @return array<string, RoleInterface>
     */
    public function getRoles(): array
    {
        return $this->roles;
    }

    /**
     * Check whether a role is allowed to access an action from a component
     *
     * ```php
     * // Does andres have access to the customers component to create?
     * $acl->isAllowed("andres", "Products", "create");
     *
     * // Do guests have access to any component to edit?
     * $acl->isAllowed("guests", "*", "edit");
     * ```
     *
     * @param RoleAwareInterface|RoleInterface|string           $roleName
     * @param ComponentAwareInterface|ComponentInterface|string $componentName
     * @param string                                            $access
     * @param array<int|string, mixed>                          $parameters
     *
     * @return bool
     * @throws Exception
     * @throws ReflectionException
     * @throws EventsException
     */
    public function isAllowed(
        RoleAwareInterface | RoleInterface | string $roleName,
        ComponentAwareInterface | ComponentInterface | string $componentName,
        string $access,
        array $parameters = []
    ): bool {
        $componentObject = null;
        $haveAccess      = null;
        $funcAccess      = null;
        $roleObject      = null;
        $hasComponent    = false;
        $hasRole         = false;

        if (is_object($roleName)) {
            if ($roleName instanceof RoleAwareInterface) {
                $roleObject = $roleName;
                $roleName   = $roleName->getRoleName();
            } else {
                $roleName = $roleName->getName();
            }
        }

        if (is_object($componentName)) {
            if ($componentName instanceof ComponentAwareInterface) {
                $componentObject = $componentName;
                $componentName   = $componentName->getComponentName();
            } else {
                $componentName = $componentName->getName();
            }
        }

        $this->activeRole      = $roleName;
        $this->activeComponent = $componentName;
        $this->activeAccess    = $access;
        $this->activeKey       = null;
        $this->activeFunction  = null;

        $this->activeFunctionCustomArgumentsCount = 0;

        if (false === $this->fireManagerEvent('acl:beforeCheckAccess')) {
            return false;
        }

        /**
         * Check if the role exists
         */
        if (!isset($this->roles[$roleName])) {
            return $this->defaultAccess == Enum::ALLOW;
        }

        /**
         * Check if there is a direct combination for role-component-access
         */
        $accessKey = $this->canAccess($roleName, $componentName, $access);
        if (null !== $accessKey && isset($this->access[$accessKey])) {
            $haveAccess = $this->access[$accessKey];
            $funcAccess = $this->functions[$accessKey] ?? null;
        }

        /**
         * Check in the inherits roles
         */
        $this->accessGranted = (null === $haveAccess) ? false : $haveAccess;
        $this->fireManagerEvent('acl:afterCheckAccess');

        $this->activeKey      = $accessKey ?: null;
        $this->activeFunction = $funcAccess;

        if (null === $haveAccess) {
            /**
             * Change activeKey to most narrow if there was no access for any
             * patterns found
             */
            $this->activeKey = $roleName . '!' . $componentName . '!' . $access;

            return $this->defaultAccess === Enum::ALLOW;
        }

        /**
         * If we have funcAccess then do all the checks for it
         */
        if (is_callable($funcAccess)) {
            $reflectionFunction   = new ReflectionFunction($funcAccess);
            $reflectionParameters = $reflectionFunction->getParameters();
            $parameterNumber      = count($reflectionParameters);

            /**
             * No parameters, just return haveAccess and call function without
             * array
             */
            if (0 === $parameterNumber) {
                return $haveAccess == Enum::ALLOW && call_user_func(
                    $funcAccess
                );
            }

            $parametersForFunction      = [];
            $numberOfRequiredParameters = $reflectionFunction->getNumberOfRequiredParameters();
            $userParametersSizeShouldBe = $parameterNumber;
            foreach ($reflectionParameters as $reflectionParameter) {
                /** @var ReflectionNamedType $reflectionType */
                $reflectionType   = $reflectionParameter->getType();
                $parameterToCheck = $reflectionParameter->getName();

                if (null != $reflectionType) {
                    /** @var class-string $className */
                    $className       = $reflectionType->getName();
                    $reflectionClass = new ReflectionClass($className);

                    // roleObject is this class
                    if (
                        null !== $roleObject &&
                        true === $reflectionClass->isInstance($roleObject) &&
                        true !== $hasRole
                    ) {
                        $hasRole                 = true;
                        $parametersForFunction[] = $roleObject;
                        $userParametersSizeShouldBe--;

                        continue;
                    }

                    // componentObject is this class
                    if (
                        null !== $componentObject &&
                        true === $reflectionClass->isInstance(
                            $componentObject
                        ) &&
                        true !== $hasComponent
                    ) {
                        $hasComponent            = true;
                        $parametersForFunction[] = $componentObject;
                        $userParametersSizeShouldBe--;

                        continue;
                    }

                    /**
                     * This is some user defined class, check if his parameter
                     * is instance of it
                     */
                    if (
                        isset($parameters[$parameterToCheck]) &&
                        is_object($parameters[$parameterToCheck]) &&
                        true !== $reflectionClass->isInstance(
                            $parameters[$parameterToCheck]
                        )
                    ) {
                        throw new Exception(
                            'Your passed parameter does not have the ' .
                            'same class as the parameter in defined function ' .
                            'when checking if ' . $roleName . ' can ' . $access .
                            ' ' . $componentName . '. Class passed: ' .
                            get_class($parameters[$parameterToCheck]) .
                            ' , Class in defined function: ' .
                            $reflectionClass->getName() . '.'
                        );
                    }
                }

                if (isset($parameters[$parameterToCheck])) {
                    /**
                     * We can't check type of ReflectionParameter in PHP 5.x so
                     * we just add it as it is
                     */
                    $parametersForFunction[] = $parameters[$parameterToCheck];
                }
            }

            $this->activeFunctionCustomArgumentsCount = $userParametersSizeShouldBe;

            if (count($parameters) > $userParametersSizeShouldBe) {
                trigger_error(
                    "Number of parameters in array is higher than " .
                    "the number of parameters in defined function when checking if '" .
                    $roleName . "' can '" . $access . "' '" . $componentName .
                    "'. Extra parameters will be ignored.",
                    E_USER_WARNING
                );
            }

            // We dont have any parameters so check default action
            if (empty($parametersForFunction)) {
                if ($numberOfRequiredParameters > 0) {
                    trigger_error(
                        "You did not provide any parameters when '" .
                        $roleName . "' can '" . $access .
                        "' '" . $componentName .
                        "'. We will use default action when no arguments."
                    );

                    return $haveAccess == Enum::ALLOW &&
                        $this->noArgumentsDefaultAction == Enum::ALLOW;
                }

                /**
                 * Number of required parameters == 0 so call funcAccess without
                 * any arguments
                 */
                return $haveAccess == Enum::ALLOW &&
                    call_user_func($funcAccess);
            }

            // Check necessary parameters
            if (count($parametersForFunction) >= $numberOfRequiredParameters) {
                return $haveAccess == Enum::ALLOW &&
                    call_user_func_array($funcAccess, $parametersForFunction);
            }

            // We don't have enough parameters
            throw new Exception(
                "You did not provide all necessary parameters for the " .
                "defined function when checking if '" . $roleName . "' can '" .
                $access . "' for '" . $componentName . "'."
            );
        }

        return $haveAccess == Enum::ALLOW;
    }

    /**
     * Check whether component exist in the components list
     *
     * @param string $componentName
     *
     * @return bool
     */
    public function isComponent(string $componentName): bool
    {
        return isset($this->components[$componentName]);
    }

    /**
     * Check whether role exist in the roles list
     *
     * @param string $roleName
     *
     * @return bool
     */
    public function isRole(string $roleName): bool
    {
        return isset($this->roles[$roleName]);
    }

    /**
     * Sets the default access level (`Phalcon\Enum::ALLOW` or
     * `Phalcon\Enum::DENY`) for no arguments provided in isAllowed action if
     * there exists func for accessKey
     *
     * @param int $defaultAccess
     */
    public function setNoArgumentsDefaultAction(int $defaultAccess): void
    {
        $this->noArgumentsDefaultAction = $defaultAccess;
    }

    /**
     * Checks if a role has access to a component
     *
     * @param string               $roleName
     * @param string               $componentName
     * @param array<string>|string $access
     * @param int                  $action
     * @param callable|null        $function
     *
     * @throws Exception
     */
    private function allowOrDeny(
        string $roleName,
        string $componentName,
        array | string $access,
        int $action,
        callable | null $function = null
    ): void {
        $this->checkExists($this->roles, $roleName, 'Role');
        $this->checkExists($this->componentsNames, $componentName, 'Component');

        if (is_array($access)) {
            foreach ($access as $accessName) {
                $this->checkExistsInAccessList($componentName, $accessName);
            }

            foreach ($access as $accessName) {
                $accessKey                = $roleName . '!' . $componentName . '!' . $accessName;
                $this->access[$accessKey] = ($action === Enum::ALLOW);
                if (null !== $function) {
                    $this->functions[$accessKey] = $function;
                }
            }
        } else {
            if ('*' !== $access) {
                $this->checkExistsInAccessList($componentName, $access);
            }

            $accessKey                = $roleName . '!' . $componentName . '!' . $access;
            $this->access[$accessKey] = ($action === Enum::ALLOW);
            if (null !== $function) {
                $this->functions[$accessKey] = $function;
            }
        }
    }

    /**
     * Check whether a role is allowed to access an action from a component
     *
     * @param string $roleName
     * @param string $componentName
     * @param string $access
     *
     * @return string|null
     */
    private function canAccess(
        string $roleName,
        string $componentName,
        string $access
    ): string | null {
        /**
         * Check if there is a direct combination for role-component-access
         */
        $keys   = $this->getKeys($roleName, $componentName, $access);
        $result = array_intersect_key($keys, $this->access);
        if (!empty($result)) {
            return key($result);
        }

        if (isset($this->roleInherits[$roleName])) {
            $checkRoleToInherits = $this->roleInherits[$roleName];
            $usedRoleToInherits  = [];

            /**
             * Deep check if the role to inherit is valid
             */
            while (!empty($checkRoleToInherits)) {
                $checkRoleToInherit = array_shift($checkRoleToInherits);

                if (isset($usedRoleToInherits[$checkRoleToInherit])) {
                    continue;
                }

                $usedRoleToInherits[$checkRoleToInherit] = true;

                /**
                 * Check if there is a direct combination in one of the
                 * inherited roles
                 */
                $keys   = $this->getKeys($checkRoleToInherit, $componentName, $access);
                $result = array_intersect_key($keys, $this->access);
                if (!empty($result)) {
                    return key($result);
                }

                /**
                 * Push inherited roles
                 */
                if (isset($this->roleInherits[$checkRoleToInherit])) {
                    $checkRoleToInherits = array_merge($checkRoleToInherits, $this->roleInherits[$checkRoleToInherit]);
                }
            }
        }

        return null;
    }

    /**
     * @param array<string, mixed> $collection
     * @param string               $element
     * @param string               $elementName
     * @param string               $suffix
     *
     * @throws Exception
     */
    private function checkExists(
        array $collection,
        string $element,
        string $elementName,
        string $suffix = 'ACL'
    ): void {
        if (!isset($collection[$element])) {
            throw new Exception(
                $elementName . " '" . $element .
                "' does not exist in the " . $suffix
            );
        }
    }

    /**
     * @param string $componentName
     * @param string $accessName
     *
     * @return void
     * @throws Exception
     */
    private function checkExistsInAccessList(
        string $componentName,
        string $accessName
    ): void {
        $accessKey = $componentName . '!' . $accessName;
        if (!isset($this->accessList[$accessKey])) {
            throw new Exception(
                "Access '" . $accessName .
                "' does not exist in component '" .
                $componentName . "'"
            );
        }
    }

    /**
     * @param string $roleName
     * @param string $componentName
     * @param string $access
     *
     * @return true[]
     */
    private function getKeys(
        string $roleName,
        string $componentName,
        string $access
    ): array {
        return [
            $roleName . '!' . $componentName . '!' . $access => true,
            $roleName . '!' . $componentName . '!*'          => true,
            $roleName . '!*!*'                               => true,
        ];
    }
}
