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

namespace Phalcon\Contracts\Assets;

/**
 * Canonical contract for Phalcon\Assets\Asset.
 *
 * Covers collection membership: an asset's key, type, HTML attributes, and
 * filter flag. The file-output pipeline (Phalcon\Assets\Manager::output())
 * requires the concrete Phalcon\Assets\Asset class.
 */
interface Asset
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
     * @return array<string, string>|null
     */
    public function getAttributes(): array | null;

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
     * @return Asset
     */
    public function setAttributes(array $attributes): Asset;

    /**
     * Sets if the asset must be filtered or not.
     *
     * @param bool $filter
     *
     * @return Asset
     */
    public function setFilter(bool $filter): Asset;

    /**
     * Sets the asset's type.
     *
     * @param string $type
     *
     * @return Asset
     */
    public function setType(string $type): Asset;
}
