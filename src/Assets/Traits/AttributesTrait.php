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

namespace Phalcon\Assets\Traits;

/**
 * Shared HTML-attributes state for asset objects (`Asset`, `Inline`,
 * `Collection`).
 *
 * @todo v7 - share setAttributes here too (blocked: Collection is not an
 *       AssetInterface, so the return type diverges)
 */
trait AttributesTrait
{
    /**
     * @var array<string, string>
     */
    protected array $attributes = [];

    /**
     * Gets extra HTML attributes.
     *
     * @return array<string, string>
     */
    public function getAttributes(): array
    {
        return $this->attributes;
    }
}
