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
use Phalcon\Image\Exception;

use function array_map;
use function max;
use function pathinfo;
use function preg_replace;
use function round;
use function str_split;
use function strlen;
use function substr;

use const PATHINFO_EXTENSION;

/**
 * All image adapters must use this class
 */
abstract class AbstractAdapter implements AdapterInterface
{
    /**
     * @var string
     */
    protected string $file;

    /**
     * Image height
     *
     * @var int
     */
    protected int $height;

    /**
     * @var mixed|null
     */
    protected $image = null;

    /**
     * Image mime type
     *
     * @var string
     */
    protected string $mime;

    /**
     * @var string
     */
    protected string $realpath;

    /**
     * Image type
     *
     * Driver dependent
     *
     * @var int
     */
    protected int $type;

    /**
     * Image width
     *
     * @var int
     */
    protected int $width;

    /**
     * Set the background color of an image
     *
     * @param string $color
     * @param int    $opacity
     *
     * @return AdapterInterface
     */
    public function background(
        string $color,
        int $opacity = 100
    ): AdapterInterface {
        if (
            strlen($color) > 1 &&
            substr($color, 0, 1) === "#"
        ) {
            $color = substr($color, 1);
        }

        if (strlen($color) === 3) {
            $color = preg_replace("/./", "$0$0", $color);
        }

        $colors = array_map(
            "hexdec",
            str_split($color, 2)
        );

        $this->processBackground($colors[0], $colors[1], $colors[2], $opacity);

        return $this;
    }

    /**
     * Blur image
     *
     * @param int $radius
     *
     * @return AdapterInterface
     */
    public function blur(int $radius): AdapterInterface
    {
        $radius = $this->checkHighLow($radius, 1);

        $this->processBlur($radius);

        return $this;
    }

    /**
     * Crop an image to the given size
     *
     * @param int      $width
     * @param int      $height
     * @param int|null $offsetX
     * @param int|null $offsetY
     *
     * @return AdapterInterface
     */
    public function crop(
        int $width,
        int $height,
        int $offsetX = null,
        int $offsetY = null
    ): AdapterInterface {
        if (null === $offsetX) {
            $offsetX = (($this->width - $width) / 2);
        } else {
            $offsetX = ($offsetX < 0) ? $this->width - $width + $offsetX : $offsetX;
            $offsetX = ($offsetX > $this->width) ? $this->width : $offsetX;
        }

        if (null === $offsetY) {
            $offsetY = (($this->height - $height) / 2);
        } else {
            $offsetY = ($offsetY < 0) ? $this->height - $height + $offsetY : $offsetY;
            $offsetY = ($offsetY > $this->height) ? $this->height : $offsetY;
        }

        if ($width > ($this->width - $offsetX)) {
            $width = $this->width - $offsetX;
        }

        if ($height > ($this->height - $offsetY)) {
            $height = $this->height - $offsetY;
        }

        $this->processCrop($width, $height, $offsetX, $offsetY);

        return $this;
    }

    /**
     * Flip the image along the horizontal or vertical axis
     *
     * @param int $direction
     *
     * @return AdapterInterface
     */
    public function flip(int $direction): AdapterInterface
    {
        if ($direction != Enum::HORIZONTAL && $direction != Enum::VERTICAL) {
            $direction = Enum::HORIZONTAL;
        }

        $this->processFlip($direction);

        return $this;
    }

    /**
     * @return int
     */
    public function getHeight(): int
    {
        return $this->height;
    }

    /**
     * @return object|null
     */
    public function getImage()
    {
        return $this->image;
    }

    /**
     * @return string
     */
    public function getMime(): string
    {
        return $this->mime;
    }

    /**
     * @return string
     */
    public function getRealpath(): string
    {
        return $this->realpath;
    }

    /**
     * @return int
     */
    public function getType(): int
    {
        return $this->type;
    }

    /**
     * @return int
     */
    public function getWidth(): int
    {
        return $this->width;
    }

    /**
     * This method scales the images using liquid rescaling method. Only support
     * Imagick
     *
     * @param int $width
     * @param int $height
     * @param int $deltaX
     * @param int $rigidity
     *
     * @return AbstractAdapter
     */
    public function liquidRescale(
        int $width,
        int $height,
        int $deltaX = 0,
        int $rigidity = 0
    ): AbstractAdapter {
        $this->processLiquidRescale($width, $height, $deltaX, $rigidity);

        return $this;
    }

    /**
     * Composite one image onto another
     *
     * @param AdapterInterface $mask
     *
     * @return AdapterInterface
     */
    public function mask(AdapterInterface $mask): AdapterInterface
    {
        $this->processMask($mask);

        return $this;
    }

    /**
     * Pixelate image
     *
     * @param int $amount
     *
     * @return AdapterInterface
     */
    public function pixelate(int $amount): AdapterInterface
    {
        if ($amount < 2) {
            $amount = 2;
        }

        $this->processPixelate($amount);

        return $this;
    }

    /**
     * Add a reflection to an image
     *
     * @param int  $height
     * @param int  $opacity
     * @param bool $fadeIn
     *
     * @return AdapterInterface
     */
    public function reflection(
        int $height,
        int $opacity = 100,
        bool $fadeIn = false
    ): AdapterInterface {
        if ($height <= 0 || $height > $this->height) {
            $height = $this->height;
        }

        $opacity = $this->checkHighLow($opacity);

        $this->processReflection($height, $opacity, $fadeIn);

        return $this;
    }

    /**
     * Render the image and return the binary string
     *
     * @param string|null $extension
     * @param int         $quality
     *
     * @return string
     */
    public function render(string $extension = null, int $quality = 100): string
    {
        if (null === $extension) {
            $extension = (string) pathinfo($this->file, PATHINFO_EXTENSION);
        }

        if (true === empty($extension)) {
            $extension = "png";
        }

        $quality = $this->checkHighLow($quality, 1);

        return $this->processRender($extension, $quality);
    }

    /**
     * Resize the image to the given size
     *
     * @param int|null $width
     * @param int|null $height
     * @param int      $master
     *
     * @return AdapterInterface
     * @throws Exception
     */
    public function resize(
        int $width = null,
        int $height = null,
        int $master = Enum::AUTO
    ): AdapterInterface {
        switch ($master) {
            case Enum::TENSILE:
            case Enum::AUTO:
            case Enum::INVERSE:
            case Enum::PRECISE:
                if (null === $width || null === $height) {
                    throw new Exception("width and height must be specified");
                }
                break;
            case Enum::WIDTH:
                if (null === $width) {
                    throw new Exception("width must be specified");
                }
                break;
            case Enum::HEIGHT:
                if (null === $height) {
                    throw new Exception("height must be specified");
                }
                break;
        }

        if ($master !== Enum::TENSILE) {
            if ($master === Enum::AUTO) {
                $master = ($this->width / $width) > ($this->height / $height) ? Enum::WIDTH : Enum::HEIGHT;
            }

            if ($master === Enum::INVERSE) {
                $master = ($this->width / $width) > ($this->height / $height) ? Enum::HEIGHT : Enum::WIDTH;
            }

            switch ($master) {
                case Enum::WIDTH:
                    $height = $this->height * $width / $this->width;
                    break;

                case Enum::HEIGHT:
                    $width = $this->width * $height / $this->height;
                    break;

                case Enum::PRECISE:
                    $ratio = $this->width / $this->height;

                    if (($width / $height) > $ratio) {
                        $height = $this->height * $width / $this->width;
                    } else {
                        $width = $this->width * $height / $this->height;
                    }
                    break;

                case Enum::NONE:
                    $width  = (null === $width) ? $this->width : $width;
                    $height = (null === $height) ? $this->height : $height;
                    break;
            }
        }

        $width  = (int) max(round($width), 1);
        $height = (int) max(round($height), 1);

        $this->processResize($width, $height);

        return $this;
    }

    /**
     * Rotate the image by a given amount
     *
     * @param int $degrees
     *
     * @return AdapterInterface
     */
    public function rotate(int $degrees): AdapterInterface
    {
        if ($degrees > 180) {
            $degrees %= 360;

            if ($degrees > 180) {
                $degrees -= 360;
            }
        } else {
            while ($degrees < -180) {
                $degrees += 360;
            }
        }

        $this->processRotate($degrees);

        return $this;
    }

    /**
     * Save the image
     *
     * @param string|null $file
     * @param int         $quality
     *
     * @return AdapterInterface
     */
    public function save(string $file = null, int $quality = -1): AdapterInterface
    {
        if (null === $file) {
            $file = $this->realpath;
        }

        $this->processSave($file, $quality);

        return $this;
    }

    /**
     * Sharpen the image by a given amount
     *
     * @param int $amount
     *
     * @return AdapterInterface
     */
    public function sharpen(int $amount): AdapterInterface
    {
        $amount = $this->checkHighLow($amount, 1);

        $this->processSharpen($amount);

        return $this;
    }

    /**
     * Add a text to an image with a specified opacity
     *
     * @param string      $text
     * @param mixed       $offsetX
     * @param mixed       $offsetY
     * @param int         $opacity
     * @param string      $color
     * @param int         $size
     * @param string|null $fontFile
     *
     * @return AdapterInterface
     */
    public function text(
        string $text,
        $offsetX = false,
        $offsetY = false,
        int $opacity = 100,
        string $color = "000000",
        int $size = 12,
        string $fontFile = null
    ): AdapterInterface {
        $opacity = $this->checkHighLow($opacity);

        if (
            strlen($color) > 1 &&
            substr($color, 0, 1) === "#"
        ) {
            $color = substr($color, 1);
        }

        if (strlen($color) === 3) {
            $color = preg_replace("/./", "$0$0", $color);
        }

        $colors = array_map(
            "hexdec",
            str_split($color, 2)
        );

        $this->processText(
            $text,
            $offsetX,
            $offsetY,
            $opacity,
            $colors[0],
            $colors[1],
            $colors[2],
            $size,
            $fontFile
        );

        return $this;
    }

    /**
     * Add a watermark to an image with the specified opacity
     *
     * @param AdapterInterface $watermark
     * @param int              $offsetX
     * @param int              $offsetY
     * @param int              $opacity
     *
     * @return AdapterInterface
     */
    public function watermark(
        AdapterInterface $watermark,
        int $offsetX = 0,
        int $offsetY = 0,
        int $opacity = 100
    ): AdapterInterface {
        $temp = $this->width - $watermark->getWidth();
        $offsetX = $this->checkHighLow($offsetX, 0, $temp);

        $temp = $this->height - $watermark->getHeight();
        $offsetY = $this->checkHighLow($offsetX, 0, $temp);

        $opacity = $this->checkHighLow($opacity);

        $this->processWatermark($watermark, $offsetX, $offsetY, $opacity);

        return $this;
    }

    /**
     * @param int $value
     * @param int $min
     * @param int $max
     *
     * @return int
     */
    protected function checkHighLow(int $value, int $min = 0, int $max = 100): int
    {
        return min($max, max($value, $min));
    }
}
