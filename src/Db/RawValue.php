<?php

/**
 * This file is part of the Phalcon Framework.
 *
 * (c) Phalcon Team <team@phalcon.io>
 *
 * For the full copyright and license information, please view the LICENSE.txt
 * file that was distributed with this source code.
 */

namespace Phiz\Db;

/**
 * This class allows to insert/update raw data without quoting or formatting.
 *
 * The next example shows how to use the MySQL now() function as a field value.
 *
 *```php
 * $subscriber = new Subscribers();
 *
 * $subscriber->email     = "andres@phalcon.io";
 * $subscriber->createdAt = new \Phiz\Db\RawValue("now()");
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
     * Phiz\Db\RawValue constructor
     */
    public function __construct($value)
    {
        if ($value === "") {
            $this->value = "''";
        } elseif ($value === null) {
            $this->value = "NULL";
        } else {
            $this->value = (string) $value;
        }
    }

    public function getValue() : string {
        return $this->value;
    }

    public function __toString() : string
    {
        return $this->value;
    }
}
