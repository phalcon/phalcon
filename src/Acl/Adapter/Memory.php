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
use Phalcon\Events\Traits\EventsAwareTrait;
use ReflectionException;
use ReflectionFunction;

use function array_intersect_key;
use function array_keys;
use function is_array;
use function is_object;
use function is_string;
use function key;

/**
 * Manages ACL lists in memory
 *
 *```php
 * $acl = new \Phalcon\Acl\Adapter\Memory();
 *
 * $acl->setDefaultAction(
 *     \Phalcon\Acl\Enum::DENY
 * );
 *
 * // Register roles
 * $roles = [
 *     "users"  => new \Phalcon\Acl\Role("Users"),
 *     "guests" => new \Phalcon\Acl\Role("Guests"),
 * ];
 * foreach ($roles as $role) {
 *     $acl->addRole($role);
 * }
 *
 * // Private area components
 * $privateComponents = [
 *     "companies" => ["index", "search", "new", "edit", "save", "create",
 *     "delete"],
 *     "products"  => ["index", "search", "new", "edit", "save", "create",
 *     "delete"],
 *     "invoices"  => ["index", "profile"],
 * ];
 *
 * foreach ($privateComponents as $componentName => $actions) {
 *     $acl->addComponent(
 *         new \Phalcon\Acl\Component($componentName),
 *         $actions
 *     );
 * }
 *
 * // Public area components
 * $publicComponents = [
 *     "index"   => ["index"],
 *     "about"   => ["index"],
 *     "session" => ["index", "register", "start", "end"],
 *     "contact" => ["index", "send"],
 * ];
 *
 * foreach ($publicComponents as $componentName => $actions) {
 *     $acl->addComponent(
 *         new \Phalcon\Acl\Component($componentName),
 *         $actions
 *     );
 * }
 *
 * // Grant access to public areas to both users and guests
 * foreach ($roles as $role) {
 *     foreach ($publicComponents as $component => $actions) {
 *         $acl->allow($role->getName(), $component, "*");
 *     }
 * }
 *
 * // Grant access to private area to role Users
 * foreach ($privateComponents as $component => $actions) {
 *     foreach ($actions as $action) {
 *         $acl->allow("Users", $component, $action);
 *     }
 * }
 *```
 *
 * @property mixed       $access
 * @property mixed       $accessList
 * @property mixed       $activeFunction
 * @property int         $activeFunctionCustomArgumentsCount
 * @property string|null $activeKey
 * @property mixed       $components
 * @property mixed       $componentsNames
 * @property mixed       $function
 * @property int         $noArgumentsDefaultAction
 * @property mixed       $roles
 * @property mixed       $roleInherits
 * @property mixed       $rolesNames
 */
class Memory extends AbstractAdapter
{
    use EventsAwareTrait;

    /**
     * Access
     *
     * @var mixed
     */
    protected $access;

    /**
     * Access List
     *
     * @var array
     */
    protected array $accessList = [];

    /**
     * Returns latest function used to acquire access
     *
     * @var mixed
     */
    protected $activeFunction;

    /**
     * Returns number of additional arguments(excluding role and resource) for
     * active function
     *
     * @var int
     */
    protected int $activeFunctionCustomArgumentsCount = 0;

    /**
     * Returns latest key used to acquire access
     *
     * @var string|null
     */
    protected ?string $activeKey = null;

    /**
     * Components
     *
     * @var array
     */
    protected array $components = [];

    /**
     * Function List
     *
     * @var array
     */
    protected array $functions = [];

    /**
     * Default action for no arguments is allow
     *
     * @var int
     */
    protected int $noArgumentsDefaultAction = Enum::DENY;

    /**
     * Roles
     *
     * @var array
     */
    protected array $roles = [];

    /**
     * Role Inherits
     *
     * @var array
     */
    protected array $roleInherits = [];

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
     * Access names can be a particular action, by example
     * search, update, delete, etc or a list of them
     *
     * Example:
     * ```php
     * // Add a component to the the list allowing access to an action
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
     * @param mixed $componentObject
     * @param mixed $accessList
     *
     * @return bool
     * @throws Exception
     */
    public function addComponent($componentObject, $accessList): bool
    {
        $component = $this->createObject(
            $componentObject,
            ComponentInterface::class,
            Component::class,
            'component'
        );
        $name      = $component->getName();
        if (true === isset($this->components[$name])) {
            $this->components[$name] = $component;
        }

        return $this->addComponentAccess($name, $accessList);
    }

    /**
     * Adds access to components
     *
     * @param string $componentName
     * @param mixed  $accessList
     *
     * @return bool
     * @throws Exception
     */
    public function addComponentAccess(string $componentName, $accessList): bool
    {
        if (true !== isset($this->components[$componentName])) {
            throw new Exception(
                'Component "' . $componentName . '" does not exist in ACL'
            );
        }

        $accessList = $this->processAccessList($accessList);
        foreach ($accessList as $item) {
            $accessKey = $componentName . '!' . $item;
            if (true !== isset($this->accessList[$accessKey])) {
                $this->accessList[$accessKey] = true;
            }
        }

        return true;
    }

    /**
     * Do a role inherit from another existing role
     *
     * ```php
     * $acl->addRole("administrator", "consultant");
     * $acl->addRole("administrator", ["consultant", "consultant2"]);
     * ```
     *
     * @param string $roleName
     * @param mixed  $roleToInherit
     *
     * @return bool
     * @throws Exception
     */
    public function addInherit(string $roleName, $roleToInherit): bool
    {
        $this->checkObjectExists($this->roles, $roleName, 'Role');

        if (true !== isset($this->roleInherits[$roleName])) {
            $this->roleInherits[$roleName] = [];
        }

        /**
         * Type conversion
         */
        $roleToInheritList = $this->processAccessList($roleToInherit);

        /**
         * inherits
         */
        foreach ($roleToInheritList as $inheritedRole) {
            $roleInheritName = $roleToInherit;
            if ($inheritedRole instanceof RoleInterface) {
                $roleInheritName = $inheritedRole->getName();
            }

            /**
             * Check if the role to inherit is repeat
             */
            if (true === in_array($roleInheritName, $this->roleInherits[$roleName])) {
                continue;
            }

            /**
             * Check if the role to inherit is valid
             */
            if (true !== isset($this->roles[$roleInheritName])) {
                throw new Exception(
                    'Role "' . $roleInheritName .
                    '" (to inherit) does not exist in the role list'
                );
            }

            if ($roleName === $roleInheritName) {
                return false;
            }

            /**
             * Deep check if the role to inherit is valid
             */
            if (true === isset($this->roleInherits[$roleInheritName])) {
                $checkRoleToInherits = [];

                foreach ($this->roleInherits[$roleInheritName] as $usedRoleToInherit) {
                    $checkRoleToInherits[] = $usedRoleToInherit;
                }

                $usedRoleToInherits = [];

                while (true !== empty($checkRoleToInherits)) {
                    $checkRoleToInherit = array_shift($checkRoleToInherits);

                    if (true === isset($usedRoleToInherits[$checkRoleToInherit])) {
                        continue;
                    }

                    $usedRoleToInherits[$checkRoleToInherit] = true;

                    if ($roleName === $checkRoleToInherit) {
                        throw new Exception(
                            'Role "' . $roleInheritName .
                            '" (to inherit) produces an infinite loop'
                        );
                    }

                    /**
                     * Push inherited roles
                     */
                    if (true === isset($this->roleInherits[$checkRoleToInherit])) {
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
     * Adds a role to the ACL list. Second parameter allows inheriting access
     * data from other existing role
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
     * @param mixed      $roleObject
     * @param mixed|null $accessInherits
     *
     * @return bool
     * @throws Exception
     */
    public function addRole($roleObject, $accessInherits = null): bool
    {
        $role = $this->createObject(
            $roleObject,
            RoleInterface::class,
            Role::class,
            'role'
        );
        $name = $role->getName();
        if (true === isset($this->roles[$name])) {
            $this->roles[$name] = $role;
        }

        if (null !== $accessInherits) {
            return $this->addInherit($name, $accessInherits);
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
     * @param string     $roleName
     * @param string     $componentName
     * @param mixed      $access
     * @param mixed|null $function
     *
     * @throws Exception
     */
    public function allow(
        string $roleName,
        string $componentName,
        $access,
        $function = null
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
     * @param string     $roleName
     * @param string     $componentName
     * @param mixed      $access
     * @param mixed|null $function
     *
     * @throws Exception
     */
    public function deny(
        string $roleName,
        string $componentName,
        $access,
        $function = null
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
     * Removes an access from a component
     *
     * @param string $componentName
     * @param mixed  $accessList
     *
     * @throws Exception
     */
    public function dropComponentAccess(string $componentName, $accessList): void
    {
        $localAccess = $this->processAccessList($accessList);
        foreach ($localAccess as $accessName) {
            $accessKey = $componentName . '!' . $accessName;
            unset($this->accessList[$accessKey]);
        }
    }

    /**
     * @return string|null
     */
    public function getActiveKey(): ?string
    {
        return $this->activeKey;
    }

    /**
     * @return mixed
     */
    public function getActiveFunction()
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
     * Return an array with every component registered in the list
     *
     * @return array
     */
    public function getComponents(): array
    {
        return $this->components;
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
     * @return array
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
     * @param mixed      $roleName
     * @param mixed      $componentName
     * @param string     $access
     * @param array|null $parameters
     *
     * @return bool
     * @throws Exception
     * @throws ReflectionException
     */
    public function isAllowed(
        $roleName,
        $componentName,
        string $access,
        array $parameters = null
    ): bool {
        $componentObject = null;
        $haveAccess      = null;
        $funcAccess      = null;
        $roleObject      = null;
        $hasComponent    = false;
        $hasRole         = false;

        $roleName = $this->checkObjectName(
            $roleName,
            RoleInterface::class,
            RoleAwareInterface::class,
            'roleName'
        );

        $componentName = $this->checkObjectName(
            $componentName,
            ComponentInterface::class,
            ComponentAwareInterface::class,
            'componentName'
        );


        $this->activeRole      = $roleName;
        $this->activeComponent = $componentName;
        $this->activeAccess    = $access;
        $this->activeKey       = null;
        $this->activeKey       = null;
        $this->activeFunction  = null;

        $this->activeFunctionCustomArgumentsCount = 0;

        if (false === $this->fireEvent('acl:beforeCheckAccess')) {
            return false;
        }

        /**
         * Check if the role exists
         */
        if (true !== isset($this->roles[$roleName])) {
            return ($this->defaultAccess == Enum::ALLOW);
        }

        /**
         * Check if there is a direct combination for role-component-access
         */
        $accessKey = $this->canAccess($roleName, $componentName, $access);
        if (false !== $accessKey && true === isset($this->accessList[$accessKey])) {
            $haveAccess = $this->accessList[$accessKey];
            $funcAccess = $this->functions[$accessKey] ?? null;
        }

        /**
         * Check in the inherits roles
         */
        $this->accessGranted = $haveAccess;
        $this->fireEvent('acl:afterCheckAccess');

        $this->activeKey      = $accessKey;
        $this->activeFunction = $funcAccess;

        if (null === $haveAccess) {
            /**
             * Change activeKey to most narrow if there was no access for any
             * patterns found
             */
            $this->activeKey = $roleName . '!' . $componentName . '!' . $access;

            return $this->defaultAccess == Enum::ALLOW;
        }

        /**
         * If we have funcAccess then do all the checks for it
         */
        if (true === is_callable($funcAccess)) {
            $reflectionFunction   = new ReflectionFunction($funcAccess);
            $reflectionParameters = $reflectionFunction->getParameters();
            $parameterNumber      = count($reflectionParameters);

            /**
             * No parameters, just return haveAccess and call function without
             * array
             */
            if (0 === $parameterNumber) {
                return $haveAccess == Enum::ALLOW && call_user_func($funcAccess);
            }

            $parametersForFunction      = [];
            $numberOfRequiredParameters = $reflectionFunction->getNumberOfRequiredParameters();
            $userParametersSizeShouldBe = $parameterNumber;

            foreach ($reflectionParameters as $reflectionParameter) {
                $reflectionClass  = $reflectionParameter->getClass();
                $parameterToCheck = $reflectionParameter->getName();

                if (null !== $reflectionClass) {
                    // roleObject is this class
                    if (
                        null !== $roleObject &&
                        $reflectionClass->isInstance($roleObject) &&
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
                        $reflectionClass->isInstance($componentObject) &&
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
                        true === isset($parameters[$parameterToCheck]) &&
                        is_object($parameters[$parameterToCheck]) &&
                        true !== $reflectionClass->isInstance($parameters[$parameterToCheck])
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

                if (true === isset($parameters[$parameterToCheck])) {
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
                    'Number of parameters in array is higher than ' .
                    'the number of parameters in defined function when checking if "' .
                    $roleName . '" can "' . $access . '" "' . $componentName .
                    '". Extra parameters will be ignored.',
                    E_USER_WARNING
                );
            }

            // We dont have any parameters so check default action
            if (true === empty($parametersForFunction)) {
                if ($numberOfRequiredParameters > 0) {
                    trigger_error(
                        'You did not provide any parameters when "' . $roleName .
                        '" can "' . $access . '" "'  . $componentName .
                        '". We will use default action when no arguments.'
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
                'You did not provide all necessary parameters for the ' .
                'defined function when checking if "' . $roleName . '" can "' .
                $access . '" for "' . $componentName . '".'
            );
        }

        return $haveAccess == Enum::ALLOW;
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
     * @param string     $roleName
     * @param string     $componentName
     * @param mixed      $access
     * @param mixed      $action
     * @param mixed|null $function
     *
     * @throws Exception
     */
    private function allowOrDeny(
        string $roleName,
        string $componentName,
        $access,
        $action,
        $function = null
    ): void {
        $this->checkObjectExists($this->roles, $roleName, 'Role');
        $this->checkObjectExists($this->components, $componentName, 'Component');

        if (true !== is_array($access) && '*' !== $access) {
            $access = [$access];
        }

        foreach ($access as $accessName) {
            $accessKey = $componentName . '!' . $accessName;

            if (true !== isset($this->accessList[$accessKey])) {
                throw new Exception(
                    'Access "' . $accessName .
                    '" does not exist in component "' . $componentName . '"'
                );
            }
        }

        foreach ($access as $accessName) {
            $accessKey = $roleName . '!' . $componentName . '!' . $accessName;
            $this->access[$accessKey] = $action;

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
     * @return false|int|string|null
     */
    private function canAccess(
        string $roleName,
        string $componentName,
        string $access
    ) {
        $keys    = [
            $roleName . '!' . $componentName . '!' . $access => true,
            $roleName . '!' . $componentName . '!*'          => true,
            $roleName . '!*!*'                               => true,
        ];
        $results = array_intersect_key($this->accessList, $keys);
        if (true !== empty($results)) {
            /**
             * Return the first result
             */
            return key($results[0]);
        }

        /**
         * Deep check if the role to inherit is valid
         */
        if (true === isset($this->roleInherits[$roleName])) {
            $checkRoleToInherits = [];
            foreach ($this->roleInherits[$roleName] as $usedRoleToInherit) {
                $checkRoleToInherits[] = $usedRoleToInherit;
            }

            $usedRoleToInherits = [];
            while (true !== empty($checkRoleToInherits)) {
                $checkRoleToInherit = array_shift($checkRoleToInherits);

                if (true === isset($usedRoleToInherits[$checkRoleToInherit])) {
                    continue;
                }

                $usedRoleToInherits[$checkRoleToInherit] = true;

                $keys    = [
                    $checkRoleToInherit . '!' . $componentName . '!' . $access = true,
                    $checkRoleToInherit . '!' . $componentName . '!*' => true,
                    $checkRoleToInherit . '!*!*'                      => true,
                ];
                $results = array_intersect_key($this->accessList, $keys);
                if (true !== empty($results)) {
                    /**
                     * Return the first result
                     */
                    return key($results[0]);
                }

                /**
                 * Push inherited roles
                 */
                if (true === isset($this->roleInherits[$checkRoleToInherit])) {
                    foreach ($this->roleInherits[$checkRoleToInherit] as $usedRoleToInherit) {
                        $checkRoleToInherits[] = $usedRoleToInherit;
                    }
                }
            }
        }

        return false;
    }

    /**
     * Returns an object (component or role
     *
     * @param mixed  $object
     * @param string $objectInterface
     * @param string $class
     * @param string $objectName
     *
     * @return ComponentInterface|RoleInterface
     * @throws Exception
     */
    protected function createObject(
        $object,
        string $objectInterface,
        string $class,
        string $objectName
    ) {
        if ($object instanceof $objectInterface) {
            return $object;
        }

        if (is_string($object)) {
            return new $class($object);
        }

        throw new Exception(
            'The ' . $objectName . ' must be either a string or ' .
            'an object implementing ' . $objectInterface
        );
    }

    /**
     * @param mixed  $object
     * @param string $classInterface
     * @param string $awareInterface
     * @param string $messageName
     *
     * @return string
     * @throws Exception
     */
    protected function checkObjectName(
        $object,
        string $classInterface,
        string $awareInterface,
        string $messageName
    ): string {
        if (is_object($object)) {
            if ($object instanceof $classInterface) {
                return $object->getName();
            }

            if ($object instanceof $awareInterface) {
                return $object->getRoleName();
            }

            throw new Exception(
                'Object passed as ' . $messageName . ' must implement ' .
                $classInterface . ' or ' . $awareInterface
            );
        }

        return '';
    }

    /**
     * Checks the access list if it is a string or an array and throws an
     * exception if not. If it is a string it returns it back as an array
     *
     * @param mixed $accessList
     *
     * @return array|string[]
     * @throws Exception
     */
    protected function processAccessList($accessList): array
    {
        if (true !== is_array($accessList) && true !== is_string($accessList)) {
            throw new Exception('The accessList must be either a string or an array');
        }

        if (true === is_string($accessList)) {
            $accessList = [$accessList];
        }

        return $accessList;
    }

    /**
     * @param array  $collection
     * @param string $name
     * @param string $collectionName
     *
     * @throws Exception
     */
    private function checkObjectExists(
        array $collection,
        string $name,
        string $collectionName
    ): void {
        if (true !== isset($collection[$name])) {
            throw new Exception(
                $collectionName . ' "' . $name . '" does not exist in the ACL'
            );
        }
    }
}
