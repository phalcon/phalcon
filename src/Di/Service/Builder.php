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
use Phalcon\Di\Traits\DiExceptionsTrait;
use Phalcon\Di\Traits\DiInstanceTrait;

use function is_array;

/**
 * Phalcon\Di\Service\Builder
 *
 * This class builds instances based on complex definitions
 */
class Builder
{
    use DiExceptionsTrait;
    use DiInstanceTrait;

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
        array $parameters = null
    ) {
        $this->checkClassNameExists($definition);

        $className = $definition['className'];
        if (true === is_array($parameters)) {
            /**
             * Build the instance overriding the definition constructor
             * parameters
             */
            $instance = $this->createInstance($className, $parameters);
        } else {
            /**
             * Check if the argument has constructor arguments
             */
            $args     = $definition['arguments'] ?? [];
            $params   = $this->buildParameters($container, $args);
            $instance = $this->createInstance($className, $params);
        }

        /**
         * The definition has calls?
         */
        if (true === isset($definition['calls'])) {
            $this->checkSetterInjectionConstructor($instance);
            $paramCalls = $definition['calls'];
            $this->checkSetterInjectionParameters($paramCalls);

            /**
             * The method call has parameters - element already checked if
             * it is an array
             */
            foreach ($paramCalls as $methodPosition => $method) {
                $this->checkMethodCallPosition($method, $methodPosition);
                $this->checkMethodMethodExists($method, $methodPosition);

                /**
                 * Create the method call
                 */
                $methodCall = [$instance, $method["method"]];
                if (true === isset($method['arguments'])) {
                    $arguments = $method['arguments'];
                    $this->checkMethodArgumentsIsArray(
                        $arguments,
                        $methodPosition
                    );

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
            $this->checkPropertiesInjectionConstruct($instance);

            $paramCalls = $definition['properties'];
            $this->checkSetterInjectionParameters($paramCalls);

            /**
             * The method call has parameters
             */
            foreach ($paramCalls as $propertyPosition => $property) {
                $this->checkPropertyIsArray($property, $propertyPosition);
                $this->checkPropertyNameExists($property, $propertyPosition);
                $this->checkPropertyValueExists($property, $propertyPosition);

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
        $this->checkArgumentTypeExists($position, $argument);

        $type = $argument['type'];
        switch ($type) {
            /**
             * If the argument type is 'service', we obtain the service from the
             * DI
             */
            case 'service':
                $this->checkServiceParameters($argument, 'name', $position);
                $this->checkContainerIsValid($container);

                $name = $argument['name'];

                return $container->get($name);

            /**
             * If the argument type is 'parameter', we assign the value as it is
             */
            case 'parameter':
                $this->checkServiceParameters($argument, 'value', $position);
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
                $this->checkServiceParameters($argument, 'className', $position);
                $this->checkContainerIsValid($container);

                $name = $argument['className'];
                $args = $argument['arguments'] ?? null;

                return $container->get($name, $args);

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
}
