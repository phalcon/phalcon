<?php

/**
 * This file is part of the Phalcon Framework.
 *
 * (c) Phalcon Team <team@phalcon.io>
 *
 * For the full copyright and license information, please view the LICENSE.txt
 * file that was distributed with this source code.
 *
 * Implementation of this file has been influenced by CapsulePHP
 *
 * @link    https://github.com/capsulephp/di
 * @license https://github.com/capsulephp/di/blob/3.x/LICENSE.md
 */

declare(strict_types=1);

namespace Phalcon\Container\Definitions;

use Phalcon\Container\Exception\NotFound;
use Phalcon\Container\Lazy\AbstractLazy;
use Phalcon\Container\Lazy\ArrayValues;
use Phalcon\Container\Lazy\Call;
use Phalcon\Container\Lazy\CallableGet;
use Phalcon\Container\Lazy\CallableNew;
use Phalcon\Container\Lazy\CsEnv;
use Phalcon\Container\Lazy\Env;
use Phalcon\Container\Lazy\FunctionCall;
use Phalcon\Container\Lazy\Get;
use Phalcon\Container\Lazy\GetCall;
use Phalcon\Container\Lazy\IncludeFile;
use Phalcon\Container\Lazy\NewCall;
use Phalcon\Container\Lazy\NewInstance;
use Phalcon\Container\Lazy\RequireFile;
use Phalcon\Container\Lazy\StaticCall;
use stdClass;

use function class_exists;
use function interface_exists;

class Definitions extends stdClass
{
    /**
     * @param string $id
     *
     * @return mixed
     * @throws NotFound
     */
    public function __get(string $id): mixed
    {
        $definition = $this->newDefinition($id);

        if ($definition === null) {
            throw new NotFound("Value definition '$id' not found.");
        }

        $this->$id = $definition;

        return $this->$id;
    }

    /**
     * @param array $values
     *
     * @return ArrayValues
     */
    public function array(array $values = []): ArrayValues
    {
        return new ArrayValues($values);
    }

    /**
     * @param callable $callable
     *
     * @return Call
     */
    public function call(callable $callable): Call
    {
        return new Call($callable);
    }

    /**
     * @param string|AbstractLazy $id
     *
     * @return CallableGet
     */
    public function callableGet(string | AbstractLazy $id): CallableGet
    {
        return new CallableGet($id);
    }

    /**
     * @param string|AbstractLazy $id
     *
     * @return CallableNew
     */
    public function callableNew(string | AbstractLazy $id): CallableNew
    {
        return new CallableNew($id);
    }

    /**
     * @param string      $name
     * @param string|null $type
     *
     * @return CsEnv
     */
    public function csEnv(string $name, string | null $type = null): CsEnv
    {
        return new CsEnv($name, $type);
    }

    /**
     * @param string      $name
     * @param string|null $type
     *
     * @return Env
     */
    public function env(string $name, string | null $type = null): Env
    {
        return new Env($name, $type);
    }

    /**
     * @param string $function
     * @param array  $arguments
     *
     * @return FunctionCall
     */
    public function functionCall(
        string $function,
        array $arguments
    ): FunctionCall {
        return new FunctionCall($function, $arguments);
    }

    /**
     * @param string|AbstractLazy $id
     *
     * @return Get
     */
    public function get(string | AbstractLazy $id): Get
    {
        return new Get($id);
    }

    /**
     * @param string|AbstractLazy $class
     * @param string              $method
     * @param array               $arguments
     *
     * @return GetCall
     */
    public function getCall(
        string | AbstractLazy $class,
        string $method,
        array $arguments = []
    ): GetCall {
        return new GetCall($class, $method, $arguments);
    }

    /**
     * @param string|AbstractLazy $file
     *
     * @return IncludeFile
     */
    public function include(
        string | AbstractLazy $file
    ): IncludeFile {
        return new IncludeFile($file);
    }

    /**
     * @param string|AbstractLazy $id
     *
     * @return NewInstance
     */
    public function new(string | AbstractLazy $id): NewInstance
    {
        return new NewInstance($id);
    }

    /**
     * @param string|AbstractLazy $class
     * @param string              $method
     * @param array               $arguments
     *
     * @return NewCall
     */
    public function newCall(
        string | AbstractLazy $class,
        string $method,
        array $arguments = []
    ): NewCall {
        return new NewCall($class, $method, $arguments);
    }

    /**
     * @param string $type
     *
     * @return AbstractDefinition|null
     * @throws NotFound
     */
    public function newDefinition(string $type): AbstractDefinition | null
    {
        if (interface_exists($type)) {
            return new InterfaceDefinition($type);
        }

        if (class_exists($type)) {
            return (new ClassDefinition($type))->inherit($this);
        }

        return null;
    }

    /**
     * @param string|AbstractLazy $file
     *
     * @return RequireFile
     */
    public function require(
        string | AbstractLazy $file
    ): RequireFile {
        return new RequireFile($file);
    }

    /**
     * @param string|AbstractLazy $class
     * @param string              $method
     * @param array               $arguments
     *
     * @return StaticCall
     */
    public function staticCall(
        string | AbstractLazy $class,
        string $method,
        array $arguments
    ): StaticCall {
        return new StaticCall($class, $method, $arguments);
    }
}
