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
use Phalcon\Contracts\Paginator\PaginatorTypes;
use Phalcon\Traits\Support\Helper\Str\CamelizeTrait;

use function get_class;
use function method_exists;
use function trigger_error;

/**
 * Repository of current state Phalcon\Paginator\AdapterInterface::paginate()
 *
 * @phpstan-import-type paginator_aliases from PaginatorTypes
 * @phpstan-import-type paginator_properties from PaginatorTypes
 */
class Repository implements RepositoryInterface, JsonSerializable
{
    use CamelizeTrait;

    /**
     * @var paginator_aliases
     */
    protected array $aliases = [];

    /**
     * @var paginator_properties
     */
    protected array $properties = [];

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
     * @return paginator_aliases
     */
    public function getAliases(): array
    {
        return $this->aliases;
    }

    public function getCurrent(): int
    {
        return $this->getProperty(self::PROPERTY_CURRENT_PAGE, 0);
    }

    public function getFirst(): int
    {
        return $this->getProperty(self::PROPERTY_FIRST_PAGE, 0);
    }

    public function getItems(): mixed
    {
        return $this->getProperty(self::PROPERTY_ITEMS, null);
    }

    public function getLast(): int
    {
        return $this->getProperty(self::PROPERTY_LAST_PAGE, 0);
    }

    public function getLimit(): int
    {
        return $this->getProperty(self::PROPERTY_LIMIT, 0);
    }

    public function getNext(): int
    {
        return $this->getProperty(self::PROPERTY_NEXT_PAGE, 0);
    }

    public function getPrevious(): int
    {
        return $this->getProperty(self::PROPERTY_PREVIOUS_PAGE, 0);
    }

    public function getTotalItems(): int
    {
        return $this->getProperty(self::PROPERTY_TOTAL_ITEMS, 0);
    }

    /**
     * @return paginator_properties
     */
    public function jsonSerialize(): array
    {
        return $this->properties;
    }

    /**
     * @param paginator_aliases $aliases
     */
    public function setAliases(array $aliases): RepositoryInterface
    {
        $this->aliases = $aliases;

        return $this;
    }

    /**
     * @param paginator_properties $properties
     */
    public function setProperties(array $properties): RepositoryInterface
    {
        $this->properties = $properties;

        return $this;
    }

    /**
     * Gets value of property by name
     *
     * The repository is filled by the adapters, which store an int under every
     * property that has an int default, so callers passing one are handed an
     * int back.
     *
     * @return ($defaultValue is int ? int : mixed)
     */
    protected function getProperty(string $property, mixed $defaultValue = null): mixed
    {
        return $this->properties[$property] ?? $defaultValue;
    }

    /**
     * Resolve alias property name
     */
    protected function getRealNameProperty(string $property): string
    {
        return $this->aliases[$property] ?? $property;
    }
}
