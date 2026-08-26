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

namespace Phalcon\Storage\Serializer;

use __PHP_Incomplete_Class;
use Phalcon\Storage\Serializer\Exceptions\InvalidUnserializationInput;
use Phalcon\Traits\Php\SerializeTrait;

use function is_string;
use function restore_error_handler;
use function set_error_handler;

use const E_NOTICE;
use const E_WARNING;

class Php extends AbstractSerializer
{
    use SerializeTrait;

    /**
     * Classes that unserialize() may instantiate: true (any class, the PHP
     * default), false (none) or a list of class names. Stored bytes that
     * try to build another class are rejected on read.
     *
     * @var array<int, string>|bool
     */
    protected array | bool $allowedClasses = true;

    /**
     * @return array<int, string>|bool
     */
    public function getAllowedClasses(): array | bool
    {
        return $this->allowedClasses;
    }

    /**
     * Serializes data
     *
     * @return bool|float|int|string|null
     */
    public function serialize(): mixed
    {
        if (true !== $this->isSerializable($this->data)) {
            return $this->data;
        }

        return $this->phpSerialize($this->data);
    }

    /**
     * Restricts the classes that unserialize() may instantiate (see the
     * "allowed_classes" option of unserialize()).
     *
     * @param array<int, string>|bool $allowedClasses
     */
    public function setAllowedClasses(array | bool $allowedClasses): static
    {
        $this->allowedClasses = $allowedClasses;

        return $this;
    }

    /**
     * Unserializes data
     */
    public function unserialize(mixed $data): void
    {
        if (true !== $this->isSerializable($data)) {
            $this->data = $data;
        } else {
            if (!is_string($data)) {
                throw new InvalidUnserializationInput();
            }

            $warning = false;
            set_error_handler(
                function () use (&$warning): bool {
                    $warning = true;

                    return true;
                },
                E_NOTICE | E_WARNING
            );

            $result = $this->phpUnserialize(
                $data,
                ['allowed_classes' => $this->allowedClasses]
            );

            restore_error_handler();

            /**
             * A class outside the allow-list comes back as
             * __PHP_Incomplete_Class: treat it as a failed unserialize.
             */
            if (
                true === $warning
                || false === $result
                || $result instanceof __PHP_Incomplete_Class
            ) {
                $this->isSuccess = false;
                $result          = "";
            } else {
                $this->isSuccess = true;
            }

            $this->data = $result;
        }
    }
}
