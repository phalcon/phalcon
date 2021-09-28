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

namespace Phalcon\Acl\Traits;

/**
 * This class defines role entity and its description
 *
 * @package Phalcon\Acl\Traits
 *
 * @property string      $name
 * @property string|null $description
 */
trait ItemTrait
{
    /**
     * Role name
     *
     * @var string
     */
    private string $name;

    /**
     * Role description
     *
     * @var string|null
     */
    private ?string $description = null;

    /**
     * @return string|null
     */
    public function getDescription(): ?string
    {
        return $this->description;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function __toString(): string
    {
        return $this->name;
    }
}
