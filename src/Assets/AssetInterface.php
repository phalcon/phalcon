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

namespace Phalcon\Assets;

/**
 * Interface for custom Phalcon\Assets resources
 */
interface AssetInterface
{
    /**
     * Gets the asset's key.
     *
     * @return string
     */
    public function getAssetKey(): string;

    /**
     * Gets extra HTML attributes.
     *
     * @return array<string, string>
     */
    public function getAttributes(): array;

    /**
     * Gets if the asset must be filtered or not.
     *
     * @return bool
     */
    public function getFilter(): bool;

    /**
     * Gets the asset's type.
     *
     * @return string
     */
    public function getType(): string;

    /**
     * Sets extra HTML attributes.
     *
     * @param array<string, string> $attributes
     *
     * @return AssetInterface
     */
    public function setAttributes(array $attributes): AssetInterface;

    /**
     * Sets if the asset must be filtered or not.
     *
     * @param bool $filter
     *
     * @return AssetInterface
     */
    public function setFilter(bool $filter): AssetInterface;

    /**
     * Sets the asset's type.
     *
     * @param string $type
     *
     * @return AssetInterface
     */
    public function setType(string $type): AssetInterface;
}
