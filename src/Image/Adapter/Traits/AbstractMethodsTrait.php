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

namespace Phalcon\Image\Adapter\Traits;

use Phalcon\Image\Adapter\AdapterInterface;
use Phalcon\Image\Exception;

trait AbstractMethodsTrait
{
    /**
     * @param int $red
     * @param int $green
     * @param int $blue
     * @param int $opacity
     *
     * @return void
     */
    abstract protected function processBackground(
        int $red,
        int $green,
        int $blue,
        int $opacity
    ): void;

    /**
     * @param int $radius
     *
     * @return void
     */
    abstract protected function processBlur(int $radius): void;

    /**
     * @param int $width
     * @param int $height
     * @param int $offsetX
     * @param int $offsetY
     *
     * @return void
     */
    abstract protected function processCrop(
        int $width,
        int $height,
        int $offsetX,
        int $offsetY
    ): void;

    /**
     * @param int $direction
     *
     * @return void
     */
    abstract protected function processFlip(int $direction): void;

    /**
     * @param AdapterInterface $mask
     *
     * @return void
     */
    abstract protected function processMask(AdapterInterface $mask);

    /**
     * @param int $amount
     *
     * @return void
     */
    abstract protected function processPixelate(int $amount): void;

    /**
     * @param int  $height
     * @param int  $opacity
     * @param bool $fadeIn
     *
     * @return void
     */
    abstract protected function processReflection(
        int $height,
        int $opacity,
        bool $fadeIn
    ): void;

    /**
     * @param string $extension
     * @param int    $quality
     *
     * @return false|string
     * @throws Exception
     */
    abstract protected function processRender(string $extension, int $quality);

    /**
     * @param int $width
     * @param int $height
     *
     * @return void
     */
    abstract protected function processResize(int $width, int $height): void;

    /**
     * @param int $degrees
     *
     * @return void
     */
    abstract protected function processRotate(int $degrees): void;

    /**
     * @param string $file
     * @param int    $quality
     *
     * @return void
     * @throws Exception
     */
    abstract protected function processSave(string $file, int $quality): void;

    /**
     * @param int $amount
     *
     * @return void
     */
    abstract protected function processSharpen(int $amount): void;

    /**
     * @param string      $text
     * @param mixed       $offsetX
     * @param mixed       $offsetY
     * @param int         $opacity
     * @param int         $red
     * @param int         $green
     * @param int         $blue
     * @param int         $size
     * @param string|null $fontFile
     *
     * @return void
     * @throws Exception
     */
    abstract protected function processText(
        string $text,
        mixed $offsetX,
        mixed $offsetY,
        int $opacity,
        int $red,
        int $green,
        int $blue,
        int $size,
        string $fontFile = null
    ): void;

    abstract protected function processWatermark(
        AdapterInterface $watermark,
        int $offsetX,
        int $offsetY,
        int $opacity
    ): void;
}
