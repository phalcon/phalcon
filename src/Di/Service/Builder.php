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

namespace Phalcon\Di\Service;

use Phalcon\Contracts\Di\DiTypes;
use Phalcon\Di\DiInterface;
use Phalcon\Di\Exception;
use Phalcon\Di\Exceptions\ArgumentTypeRequired;
use Phalcon\Di\Exceptions\CallArgumentsMustBeArray;
use Phalcon\Di\Exceptions\MethodCallMustBeArray;
use Phalcon\Di\Exceptions\MethodNameRequired;
use Phalcon\Di\Exceptions\MissingClassNameParameter;
use Phalcon\Di\Exceptions\MissingParameterKey;
use Phalcon\Di\Exceptions\PropertyInjectionRequiresInstance;
use Phalcon\Di\Exceptions\PropertyMustBeArray;
use Phalcon\Di\Exceptions\PropertyNameRequired;
use Phalcon\Di\Exceptions\PropertyValueRequired;
use Phalcon\Di\Exceptions\SetterInjectionRequiresInstance;
use Phalcon\Di\Exceptions\SetterParametersMustBeArray;
use Phalcon\Di\Exceptions\UnknownServiceType;

use function call_user_func;
use function call_user_func_array;
use function is_array;
use function is_object;

/**
 * Phalcon\Di\Service\Builder
 *
 * This class builds instances based on complex definitions
 *
 * @phpstan-import-type di_parameters from DiTypes
 * @phpstan-import-type di_service_argument from DiTypes
 * @phpstan-import-type di_service_definition from DiTypes
 */
class Builder
{
    /**
     * Builds a service using a complex service definition
     *
     * @phpstan-param di_service_definition $definition
     * @phpstan-param di_parameters|null    $parameters
     *
     * @return mixed
     * @throws Exception
     */
    public function build(
        DiInterface $container,
        array $definition,
        array | null $parameters = null
    ) {
        /**
         * The class name is required
         */
        if (!isset($definition['className'])) {
            throw new MissingClassNameParameter();
        }

        $className = $definition['className'];

        if (is_array($parameters)) {
            /**
             * Build the instance overriding the definition constructor
             * parameters
             */
            if (!empty($parameters)) {
                $instance = new $className(...$parameters);
            } else {
                $instance = new $className();
            }
        } else {
            /**
             * Check if the argument has constructor arguments
             */
            if (isset($definition['arguments'])) {
                /**
                 * Create the instance based on the parameters
                 */
                $instance = new $className(
                    ...$this->buildParameters($container, $definition['arguments'])
                );
            } else {
                $instance = new $className();
            }
        }

        /**
         * The definition has calls?
         */
        if (isset($definition['calls'])) {
            if (!is_object($instance)) {
                throw new SetterInjectionRequiresInstance();
            }

            $paramCalls = $definition['calls'];
            if (!is_array($paramCalls)) {
                throw new SetterParametersMustBeArray();
            }

            /**
             * The method call has parameters
             */
            foreach ($paramCalls as $methodPosition => $method) {
                /**
                 * The call parameter must be an array of arrays
                 */
                if (!is_array($method)) {
                    throw new MethodCallMustBeArray($methodPosition);
                }

                /**
                 * A param 'method' is required
                 */
                if (!isset($method['method'])) {
                    throw new MethodNameRequired($methodPosition);
                }

                /**
                 * Create the method call
                 */
                $methodCall = [$instance, $method['method']];

                if (isset($method['arguments'])) {
                    $arguments = $method['arguments'];
                    if (!is_array($arguments)) {
                        throw new CallArgumentsMustBeArray((int) $methodPosition);
                    }

                    if (!empty($arguments)) {
                        /**
                         * Call the method on the instance
                         */
                        call_user_func_array(
                            $methodCall,
                            $this->buildParameters($container, $arguments)
                        );

                        /**
                         * Go to next method call
                         */
                        continue;
                    }
                }

                /**
                 * Call the method on the instance without arguments
                 */
                call_user_func($methodCall);
            }
        }

        /**
         * The definition has properties?
         */
        if (isset($definition['properties'])) {
            if (!is_object($instance)) {
                throw new PropertyInjectionRequiresInstance();
            }

            $paramCalls = $definition['properties'];
            if (!is_array($paramCalls)) {
                throw new SetterParametersMustBeArray();
            }

            /**
             * The method call has parameters
             */
            foreach ($paramCalls as $propertyPosition => $property) {
                /**
                 * The call parameter must be an array of arrays
                 */
                if (!is_array($property)) {
                    throw new PropertyMustBeArray($propertyPosition);
                }

                /**
                 * A param 'name' is required
                 */
                if (!isset($property['name'])) {
                    throw new PropertyNameRequired($propertyPosition);
                }

                /**
                 * A param 'value' is required
                 */
                if (!isset($property['value'])) {
                    throw new PropertyValueRequired($propertyPosition);
                }

                /**
                 * Update the public property
                 */
                $propertyName  = $property['name'];
                $propertyValue = $property['value'];

                $instance->$propertyName = $this->buildParameter(
                    $container,
                    $propertyPosition,
                    $propertyValue
                );
            }
        }

        return $instance;
    }

    /**
     * Resolves a constructor/call parameter
     *
     * @phpstan-param di_service_argument $argument
     *
     * @return mixed
     * @throws Exception
     */
    private function buildParameter(
        DiInterface $container,
        int $position,
        array $argument
    ) {
        /**
         * All the arguments must have a type
         */
        if (!isset($argument['type'])) {
            throw new ArgumentTypeRequired($position);
        }

        switch ($argument['type']) {
            case 'service':
                /**
                 * If the argument type is 'service', we obtain the service from the
                 * DI
                 */
                if (!isset($argument['name'])) {
                    throw new MissingParameterKey('name', $position);
                }

                return $container->get($argument['name']);

            case 'parameter':
                /**
                 * If the argument type is 'parameter', we assign the value as it is
                 */
                if (!isset($argument['value'])) {
                    throw new MissingParameterKey('value', $position);
                }

                return $argument['value'];

            case 'instance':
                /**
                 * If the argument type is 'instance', we assign the value as it is
                 */
                if (!isset($argument['className'])) {
                    throw new MissingParameterKey('className', $position);
                }

                if (isset($argument['arguments'])) {
                    /**
                     * Build the instance with arguments
                     */
                    return $container->get(
                        $argument['className'],
                        $argument['arguments']
                    );
                }

                /**
                 * The instance parameter does not have arguments for its
                 * constructor
                 */
                return $container->get($argument['className']);

            default:
                /**
                 * Unknown parameter type
                 */
                throw new UnknownServiceType($position);
        }
    }

    /**
     * Resolves an array of parameters
     *
     * @phpstan-param array<int, di_service_argument> $arguments
     *
     * @phpstan-return list<mixed>
     *
     * @throws Exception
     */
    private function buildParameters(
        DiInterface $container,
        array $arguments
    ): array {
        $buildArguments = [];

        foreach ($arguments as $position => $argument) {
            $buildArguments[] = $this->buildParameter(
                $container,
                $position,
                $argument
            );
        }

        return $buildArguments;
    }
}
