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

use Phalcon\Di\DiInterface;
use Phalcon\Di\Exception;

use function is_array;
use function is_object;

/**
 * Phalcon\Di\Service\Builder
 *
 * This class builds instances based on complex definitions
 */
class Builder
{
    /**
     * Builds a service using a complex service definition
     *
     * @param DiInterface $container
     * @param array       $definition
     * @param array|null  $parameters
     *
     * @return mixed
     * @throws Exception
     */
    public function build(
        DiInterface $container,
        array $definition,
        $parameters = null
    ) {
        /**
         * The class name is required
         */
        if (true !== isset($definition['className'])) {
            throw new Exception(
                'Invalid service definition. Missing "className" parameter'
            );
        }

        $className = $definition['className'];
        if (true === is_array($parameters)) {
            /**
             * Build the instance overriding the definition constructor
             * parameters
             */
            if (count($parameters) > 0) {
                $instance = new $className(...$parameters);
            } else {
                $instance = new $className();
            }
        } else {
            /**
             * Check if the argument has constructor arguments
             */
            if (true === isset($definition['arguments'])) {
                /**
                 * Create the instance based on the parameters
                 */
                $params   = $this->buildParameters(
                    $container,
                    $definition['arguments']
                );
                $instance = new $className(...$params);
            } else {
                $instance = new $className();
            }
        }

        /**
         * The definition has calls?
         */
        if (true === isset($definition['calls'])) {
            if (true !== is_object($instance)) {
                throw new Exception(
                    "The definition has setter injection " .
                    "parameters but the constructor didn't return an instance"
                );
            }

            $paramCalls = $definition['calls'];
            if (true !== is_array($paramCalls)) {
                throw new Exception(
                    'Setter injection parameters must be an array'
                );
            }

            /**
             * The method call has parameters
             */
            foreach ($paramCalls as $methodPosition => $method) {
                /**
                 * The call parameter must be an array of arrays
                 */
                if (true !== is_array($method)) {
                    throw new Exception(
                        'Method call must be an array on position ' .
                        $methodPosition
                    );
                }

                /**
                 * A param 'method' is required
                 */
                if (true !== isset($method['method'])) {
                    throw new Exception(
                        "The method name is required on position " .
                        $methodPosition
                    );
                }

                /**
                 * Create the method call
                 */
                $methodCall = [$instance, $method["method"]];
                if (true === isset($method['arguments'])) {
                    $arguments = $method['arguments'];
                    if (true !== is_array($arguments)) {
                        throw new Exception(
                            "Call arguments must be an array " .
                            $methodPosition
                        );
                    }

                    if (count($arguments) > 0) {
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
        if (true === isset($definition['properties'])) {
            if (true !== is_object($instance)) {
                throw new Exception(
                    "The definition has properties injection " .
                    "parameters but the constructor didn't return an instance"
                );
            }

            $paramCalls = $definition['properties'];
            if (true !== is_array($paramCalls)) {
                throw new Exception(
                    'Setter injection parameters must be an array'
                );
            }

            /**
             * The method call has parameters
             */
            foreach ($paramCalls as $propertyPosition => $property) {
                /**
                 * The call parameter must be an array of arrays
                 */
                if (true !== is_array($property)) {
                    throw new Exception(
                        "Property must be an array on position " .
                        $propertyPosition
                    );
                }

                /**
                 * A param 'name' is required
                 */
                if (true !== isset($property['name'])) {
                    throw new Exception(
                        'The property name is required on position ' .
                        $propertyPosition
                    );
                }

                /**
                 * A param 'value' is required
                 */
                if (true !== isset($property['value'])) {
                    throw new Exception(
                        'The property value is required on position ' .
                        $propertyPosition
                    );
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
     * @param DiInterface $container
     * @param int         $position
     * @param array       $argument
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
        if (true !== isset($argument['type'])) {
            throw new Exception(
                'Argument at position ' . $position . ' must have a type'
            );
        }

        $type = $argument['type'];
        switch ($type) {
            /**
             * If the argument type is 'service', we obtain the service from the
             * DI
             */
            case 'service':
                $this->checkParameters($argument, 'name', $position);
                $this->checkContainer($container);

                $name = $argument['name'];

                return $container->get($name);

            /**
             * If the argument type is 'parameter', we assign the value as it is
             */
            case 'parameter':
                $this->checkParameters($argument, 'value', $position);
                if (true !== isset($argument['value'])) {
                    throw new Exception(
                        "Service 'value' is required in parameter " .
                        "on position " . $position
                    );
                }

                return $argument['value'];

            /**
             * If the argument type is 'instance', we assign the value as it is
             */
            case 'instance':
                $this->checkParameters($argument, 'className', $position);
                $this->checkContainer($container);

                $name = $argument['className'];
                if (true === isset($argument['arguments'])) {
                    $instanceArguments = $argument['arguments'];
                    /**
                     * Build the instance with arguments
                     */
                    return $container->get($name, $instanceArguments);
                }

                /**
                 * The instance parameter does not have arguments for its
                 * constructor
                 */
                return $container->get($name);

            default:
                /**
                 * Unknown parameter type
                 */
                throw new Exception(
                    'Unknown service type in parameter on ' .
                    'position ' . $position
                );
        }
    }

    /**
     * Resolves an array of parameters
     *
     * @param DiInterface $container
     * @param array       $arguments
     *
     * @return array
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

    /**
     * @param mixed $container
     *
     * @throws Exception
     */
    private function checkContainer($container): void
    {
        if (true !== is_object($container)) {
            throw new Exception(
                'The dependency injector container is not valid'
            );
        }
    }

    /**
     * @param array  $argument
     * @param string $name
     * @param int    $position
     *
     * @throws Exception
     */
    private function checkParameters(
        array $argument,
        string $name,
        int $position
    ): void {
        if (true !== isset($argument[$name])) {
            throw new Exception(
                'Service "' . $name . '" is required in parameter ' .
                'on position ' . (string) $position
            );
        }
    }
}
