<?php

/*
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
use Phalcon\Support\Helper\Str\Camelize;

/**
 * Phalcon\Paginator\Repository
 * Repository of current state Phalcon\Paginator\AdapterInterface::paginate()
 */
class Repository implements RepositoryInterface, JsonSerializable
{
    protected array $aliases = [];

    protected array $properties = [];

    /**
     * @param string $property
     *
     * @return mixed
     */
    public function __get(string $property): mixed
    {
        $camelize = new Camelize();
        $method = "get" . $camelize($this->getRealNameProperty($property));

        if (method_exists($this, $method)) {
            return $this->{$method}();
        }

        /**
         * A notice is shown if the property is not defined
         */
        trigger_error("Access to undefined property " . get_class($this) . "::" . $property);

        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function getAliases(): array
    {
        return $this->aliases;
    }

    /**
     * {@inheritdoc}
     */
    public function getCurrent(): int
    {
        return $this->getProperty(self::PROPERTY_CURRENT_PAGE, 0);
    }

    /**
     * {@inheritdoc}
     */
    public function getFirst(): int
    {
        return $this->getProperty(self::PROPERTY_FIRST_PAGE, 0);
    }

    /**
     * {@inheritdoc}
     */
    public function getItems(): mixed
    {
        return $this->getProperty(self::PROPERTY_ITEMS);
    }

    /**
     * {@inheritdoc}
     */
    public function getLast(): int
    {
        return $this->getProperty(self::PROPERTY_LAST_PAGE, 0);
    }

    /**
     * {@inheritdoc}
     */
    public function getLimit(): int
    {
        return $this->getProperty(self::PROPERTY_LIMIT, 0);
    }

    /**
     * {@inheritdoc}
     */
    public function getNext(): int
    {
        return $this->getProperty(self::PROPERTY_NEXT_PAGE, 0);
    }

    /**
     * {@inheritdoc}
     */
    public function getPrevious(): int
    {
        return $this->getProperty(self::PROPERTY_PREVIOUS_PAGE, 0);
    }

    /**
     * {@inheritdoc}
     */
    public function getTotalItems(): int
    {
        return $this->getProperty(self::PROPERTY_TOTAL_ITEMS, 0);
    }

    /**
     * See [jsonSerialize](https://php.net/manual/en/jsonserializable.jsonserialize.php)
     */
    public function jsonSerialize(): array
    {
        return $this->properties;
    }

    /**
     * {@inheritdoc}
     */
    public function setAliases(array $aliases): RepositoryInterface
    {
        $this->aliases = $aliases;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function setProperties(array $properties): RepositoryInterface
    {
        $this->properties = $properties;

        return $this;
    }

    /**
     * Gets value of property by name
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
        $aliases = $this->getAliases();

        if (isset($aliases[$property])) {
            return $aliases[$property];
        }

        return $property;
    }
}
