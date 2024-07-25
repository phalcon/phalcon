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

namespace Phalcon\Paginator;

use JsonSerializable;
use Phalcon\Traits\Helper\Str\CamelizeTrait;

use function get_class;
use function method_exists;
use function trigger_error;

/**
 * Repository of current state Phalcon\Paginator\AdapterInterface::paginate()
 */
class Repository implements RepositoryInterface, JsonSerializable
{
    use CamelizeTrait;

    /**
     * @var array
     */
    protected array $aliases = [];

    /**
     * @var array
     */
    protected array $properties = [];

    /**
     * @param string $property
     *
     * @return mixed
     */
    public function __get(string $property): mixed
    {
        $method = "get" . $this->toCamelize(
            $this->getRealNameProperty($property)
        );

        if (method_exists($this, $method)) {
            return $this->$method();
        }

        /**
         * A notice is shown if the property is not defined
         */
        trigger_error(
            "Access to undefined property "
            . get_class($this) . "::" . $property
        );

        return null;
    }

    /**
     * @return array
     */
    public function getAliases(): array
    {
        return $this->aliases;
    }

    /**
     * @return int
     */
    public function getCurrent(): int
    {
        return $this->getProperty(self::PROPERTY_CURRENT_PAGE, 0);
    }

    /**
     * @return int
     */
    public function getFirst(): int
    {
        return $this->getProperty(self::PROPERTY_FIRST_PAGE, 0);
    }

    /**
     * @return mixed
     */
    public function getItems(): mixed
    {
        return $this->getProperty(self::PROPERTY_ITEMS, null);
    }

    /**
     * @return int
     */
    public function getLast(): int
    {
        return $this->getProperty(self::PROPERTY_LAST_PAGE, 0);
    }

    /**
     * @return int
     */
    public function getLimit(): int
    {
        return $this->getProperty(self::PROPERTY_LIMIT, 0);
    }

    /**
     * @return int
     */
    public function getNext(): int
    {
        return $this->getProperty(self::PROPERTY_NEXT_PAGE, 0);
    }

    /**
     * @return int
     */
    public function getPrevious(): int
    {
        return $this->getProperty(self::PROPERTY_PREVIOUS_PAGE, 0);
    }

    /**
     * @return int
     */
    public function getTotalItems(): int
    {
        return $this->getProperty(self::PROPERTY_TOTAL_ITEMS, 0);
    }

    /**
     * @return array
     */
    public function jsonSerialize(): array
    {
        return $this->properties;
    }

    /**
     * @param array $aliases
     *
     * @return RepositoryInterface
     */
    public function setAliases(array $aliases): RepositoryInterface
    {
        $this->aliases = $aliases;

        return $this;
    }

    /**
     * @param array $properties
     *
     * @return RepositoryInterface
     */
    public function setProperties(array $properties): RepositoryInterface
    {
        $this->properties = $properties;

        return $this;
    }

    /**
     * Gets value of property by name
     *
     * @param string     $property
     * @param mixed|null $defaultValue
     *
     * @return mixed
     */
    protected function getProperty(string $property, mixed $defaultValue = null): mixed
    {
        return $this->properties[$property] ?? $defaultValue;
    }

    /**
     * Resolve alias property name
     *
     * @param string $property
     *
     * @return string
     */
    protected function getRealNameProperty(string $property): string
    {
        return $this->aliases[$property] ?? $property;
    }
}
