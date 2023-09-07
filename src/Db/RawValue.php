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

namespace Phalcon\Db;

/**
 * This class allows to insert/update raw data without quoting or formatting.
 *
 * The next example shows how to use the MySQL now() function as a field value.
 *
 *```php
 * $subscriber = new Subscribers();
 *
 * $subscriber->email     = "andres@phalcon.io";
 * $subscriber->createdAt = new \Phalcon\Db\RawValue("now()");
 *
 * $subscriber->save();
 *```
 */
class RawValue
{
    /**
     * Raw value without quoting or formatting
     *
     * @var string
     */
    protected string $value;

    /**
     * Phalcon\Db\RawValue constructor
     */
    public function __construct(mixed $value = null)
    {
        if ("" === $value) {
            $this->value = "''";
        } elseif (null === $value) {
            $this->value = "NULL";
        } else {
            $this->value = (string)$value;
        }
    }

    /**
     * @return string
     */
    public function __toString(): string
    {
        return $this->value;
    }

    /**
     * @return string
     */
    public function getValue(): string
    {
        return $this->value;
    }
}
