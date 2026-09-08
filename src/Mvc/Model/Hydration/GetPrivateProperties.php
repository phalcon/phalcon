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

namespace Phalcon\Mvc\Model\Hydration;

class GetPrivateProperties
{
    /**
     * Per-process cache of declared private model properties as
     * [class name => [property name => ReflectionProperty]], used during
     * hydration - see getPrivateProperties()
     *
     * @phpstan-var array<class-string, array<string, \ReflectionProperty>>
     */
    private static array $privatePropertiesCache = [];

    /**
     * Returns the declared private properties of a class (including inherited
     * ones) as [property name => ReflectionProperty], cached per class.
     *
     * Hydration (cloneResult/cloneResultMap) cannot write private properties
     * directly: the write from Model scope falls back to __set(), which
     * invokes a possible setter - or throws for a non-public property
     * without one. Writing through ReflectionProperty stores the raw
     * database value instead.
     *
     * @return array<string, \ReflectionProperty>
     *
     * @see https://github.com/phalcon/cphalcon/issues/16454
     *
     * @phpstan-param class-string $className
     */
    public static function getPrivateProperties(string $className): array
    {
        if (!isset(self::$privatePropertiesCache[$className])) {
            $privateProperties = [];
            $reflection        = new \ReflectionClass($className);

            while ($reflection instanceof \ReflectionClass) {
                $reflectionProperties = $reflection->getProperties(
                    \ReflectionProperty::IS_PRIVATE
                );

                foreach ($reflectionProperties as $reflectionProperty) {
                    if ($reflectionProperty->isStatic()) {
                        continue;
                    }

                    $propertyName = $reflectionProperty->getName();

                    if (!isset($privateProperties[$propertyName])) {
                        $privateProperties[$propertyName] = $reflectionProperty;
                    }
                }

                $reflection = $reflection->getParentClass();
            }

            self::$privatePropertiesCache[$className] = $privateProperties;
        }

        return self::$privatePropertiesCache[$className];
    }
}
