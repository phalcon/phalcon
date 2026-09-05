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

namespace Phalcon\Image\Adapter;

use Phalcon\Image\Enum;

/**
 * Interface for Phalcon\Image\Adapter classes
 */
interface AdapterInterface
{
    /**
     * Add a background to an image
     */
    public function background(
        string $color,
        int $opacity = 100
    ): AdapterInterface;

    /**
     * Blur an image
     */
    public function blur(int $radius): AdapterInterface;

    /**
     * Crop an image
     */
    public function crop(
        int $width,
        int $height,
        int | null $offsetX = null,
        int | null $offsetY = null
    ): AdapterInterface;

    /**
     * Flip an image
     */
    public function flip(int $direction): AdapterInterface;

    public function getHeight(): int;

    public function getWidth(): int;

    /**
     * Add a mask to an image
     */
    public function mask(AdapterInterface $mask): AdapterInterface;

    /**
     * Pixelate an image
     */
    public function pixelate(int $amount): AdapterInterface;

    /**
     * Reflect an image
     */
    public function reflection(
        int $height,
        int $opacity = 100,
        bool $fadeIn = false
    ): AdapterInterface;

    /**
     * Render an image
     */
    public function render(
        string | null $extension = null,
        int $quality = 100
    ): string;

    /**
     * Resize an image
     */
    public function resize(
        int | null $width = null,
        int | null $height = null,
        int $master = Enum::AUTO
    ): AdapterInterface;

    /**
     * Rotate an image
     */
    public function rotate(int $degrees): AdapterInterface;

    /**
     * Save an image
     */
    public function save(
        string | null $file = null,
        int $quality = 100
    ): AdapterInterface;

    /**
     * Sharpen an image
     */
    public function sharpen(int $amount): AdapterInterface;

    /**
     * Adds text on an image
     */
    public function text(
        string $text,
        int $offsetX = 0,
        int $offsetY = 0,
        int $opacity = 100,
        string $color = "000000",
        int $size = 12,
        string | null $fontFile = null
    ): AdapterInterface;

    /**
     * Add a watermark on an image
     */
    public function watermark(
        AdapterInterface $watermark,
        int $offsetX = 0,
        int $offsetY = 0,
        int $opacity = 100
    ): AdapterInterface;
}
