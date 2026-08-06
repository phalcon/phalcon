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
use Phalcon\Image\Exceptions\InvalidColor;
use Phalcon\Image\Exceptions\MissingDimensions;
use Phalcon\Image\Exceptions\MissingHeight;
use Phalcon\Image\Exceptions\MissingWidth;

use function array_map;
use function max;
use function pathinfo;
use function preg_match;
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
    protected string $file;
    protected int $height;

    /**
     * @var mixed|null
     */
    protected mixed $image = null;

    protected string $mime;
    protected string $realpath;

    /**
     * Image type
     *
     * Driver dependent
     */
    protected int $type;

    /**
     * Image width
     */
    protected int $width;

    /**
     * Set the background color of an image
     *
     * @throws Exception
     */
    public function background(
        string $color,
        int $opacity = 100
    ): AdapterInterface {
        $colors = $this->parseColor($color);

        $this->processBackground($colors[0], $colors[1], $colors[2], $opacity);

        return $this;
    }

    /**
     * Blur image
     */
    public function blur(int $radius): AdapterInterface
    {
        $radius = $this->checkHighLow($radius, 1);

        $this->processBlur($radius);

        return $this;
    }

    /**
     * Crop an image to the given size
     */
    public function crop(
        int $width,
        int $height,
        int | null $offsetX = null,
        int | null $offsetY = null
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
     */
    public function flip(int $direction): AdapterInterface
    {
        if ($direction != Enum::HORIZONTAL && $direction != Enum::VERTICAL) {
            $direction = Enum::HORIZONTAL;
        }

        $this->processFlip($direction);

        return $this;
    }

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

    public function getMime(): string
    {
        return $this->mime;
    }

    public function getRealpath(): string
    {
        return $this->realpath;
    }

    public function getType(): int
    {
        return $this->type;
    }

    public function getWidth(): int
    {
        return $this->width;
    }

    /**
     * Composite one image onto another
     *
     * The mask is read through its public render() output rather than its
     * internal handle, so a mask created with a different backend composites
     * correctly. The cost is one encode/decode round trip per call, which is
     * worth knowing inside loops.
     */
    public function mask(AdapterInterface $mask): AdapterInterface
    {
        $this->processMask($mask);

        return $this;
    }

    /**
     * Pixelate image
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
     * @throws Exception
     */
    public function render(string | null $extension = null, int $quality = 100): string
    {
        if (null === $extension) {
            $extension = (string)pathinfo($this->file, PATHINFO_EXTENSION);
        }

        if (empty($extension)) {
            $extension = "png";
        }

        $quality = $this->checkHighLow($quality, 1);

        return $this->processRender($extension, $quality);
    }

    /**
     * Resize the image to the given size
     *
     * @throws Exception
     */
    public function resize(
        int | null $width = null,
        int | null $height = null,
        int $master = Enum::AUTO
    ): AdapterInterface {
        $this->checkResizeInput($width, $height, $master);

        if ($master !== Enum::TENSILE) {
            $master = $this->checkResizeMaster($width, $height, $master);

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

                default:
            }
        }

        $width  = null === $width ? 0 : $width;
        $height = null === $height ? 0 : $height;
        $width  = (int)max(round($width), 1);
        $height = (int)max(round($height), 1);

        $this->processResize($width, $height);

        return $this;
    }

    /**
     * Rotate the image by a given amount
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
     */
    public function save(string | null $file = null, int $quality = -1): AdapterInterface
    {
        if (null === $file) {
            $file = $this->realpath;
        }

        $this->processSave($file, $quality);

        return $this;
    }

    /**
     * Sharpen the image by a given amount
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
     * @throws Exception
     */
    public function text(
        string $text,
        $offsetX = false,
        $offsetY = false,
        int $opacity = 100,
        string $color = "000000",
        int $size = 12,
        string | null $fontFile = null
    ): AdapterInterface {
        $opacity = $this->checkHighLow($opacity);

        $colors = $this->parseColor($color);

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
     * The watermark is read through its public render() output rather than its
     * internal handle, so a watermark created with a different backend
     * composites correctly. The cost is one encode/decode round trip per call,
     * which is worth knowing inside loops.
     */
    public function watermark(
        AdapterInterface $watermark,
        int $offsetX = 0,
        int $offsetY = 0,
        int $opacity = 100
    ): AdapterInterface {
        $temp = $this->width - $watermark->getWidth();
        $offX = $this->checkHighLow($offsetX, 0, $temp);

        $temp = $this->height - $watermark->getHeight();
        $offY = $this->checkHighLow($offsetY, 0, $temp);

        $opacity = $this->checkHighLow($opacity);

        $this->processWatermark($watermark, $offX, $offY, $opacity);

        return $this;
    }

    protected function checkHighLow(int $value, int $min = 0, int $max = 100): int
    {
        return min($max, max($value, $min));
    }

    /**
     * Renders the supplied colour onto the image as the background. Channels
     * are 0-255; the opacity is the validated 0-100 value.
     */
    abstract protected function processBackground(
        int $red,
        int $green,
        int $blue,
        int $opacity
    ): void;

    /**
     * Applies a blur. The radius is already clamped to 1-100.
     */
    abstract protected function processBlur(int $radius): void;

    /**
     * Crops the image. Width, height and both offsets are already normalized
     * to fit within the current canvas.
     */
    abstract protected function processCrop(
        int $width,
        int $height,
        int $offsetX,
        int $offsetY
    ): void;

    /**
     * Flips the image. The direction is already normalized to
     * Enum::HORIZONTAL or Enum::VERTICAL.
     */
    abstract protected function processFlip(int $direction): void;

    /**
     * Composites the supplied image as a mask onto this one. The mask is read
     * through its public render() output, so it may be any adapter backend.
     */
    abstract protected function processMask(AdapterInterface $mask);

    /**
     * Pixelates the image. The amount is already at least 2.
     */
    abstract protected function processPixelate(int $amount): void;

    /**
     * Adds a reflection. The height is clamped to the image height and the
     * opacity to 0-100.
     */
    abstract protected function processReflection(
        int $height,
        int $opacity,
        bool $fadeIn
    ): void;

    /**
     * Renders the image to a binary string. The extension is non-empty and the
     * quality is already clamped to 1-100. Returns the encoded bytes.
     *
     * @throws Exception
     */
    abstract protected function processRender(string $extension, int $quality);

    /**
     * Resizes the image. Width and height are already resolved to positive
     * integers per the requested resize mode.
     */
    abstract protected function processResize(int $width, int $height): void;

    /**
     * Rotates the image. The degrees value is already normalized to -180..180.
     */
    abstract protected function processRotate(int $degrees): void;

    /**
     * Saves the image to the supplied file path.
     *
     * @throws Exception
     */
    abstract protected function processSave(string $file, int $quality): bool;

    /**
     * Sharpens the image. The amount is already clamped to 1-100.
     */
    abstract protected function processSharpen(int $amount): void;

    /**
     * Renders text onto the image. The opacity is clamped to 0-100 and the
     * colour is supplied as separate 0-255 channels.
     *
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
        string | null $fontFile = null
    ): void;

    /**
     * Composites the supplied watermark onto this image. Offsets and opacity
     * are already clamped to the valid range; the watermark is read through
     * its public render() output, so it may be any adapter backend.
     */
    abstract protected function processWatermark(
        AdapterInterface $watermark,
        int $offsetX,
        int $offsetY,
        int $opacity
    ): void;

    /**
     * Resize the image to the given size
     *
     * @param int|null $width
     * @param int|null $height
     * @param int      $master
     *
     * @return void
     * @throws Exception
     */
    private function checkResizeInput(
        int | null $width = null,
        int | null $height = null,
        int $master = Enum::AUTO
    ): void {
        switch ($master) {
            case Enum::TENSILE:
            case Enum::AUTO:
            case Enum::INVERSE:
            case Enum::PRECISE:
                if (null === $width || null === $height) {
                    throw new MissingDimensions();
                }
                break;
            case Enum::WIDTH:
                if (null === $width) {
                    throw new MissingWidth();
                }
                break;
            case Enum::HEIGHT:
                if (null === $height) {
                    throw new MissingHeight();
                }
                break;
            default:
        }
    }

    /**
     * @param int|null $width
     * @param int|null $height
     * @param int      $master
     *
     * @return int
     */
    private function checkResizeMaster(
        int | null $width = null,
        int | null $height = null,
        int $master = Enum::AUTO
    ) {
        if ($master === Enum::AUTO) {
            return ($this->width / $width) > ($this->height / $height)
                ? Enum::WIDTH
                : Enum::HEIGHT;
        }

        if ($master === Enum::INVERSE) {
            return ($this->width / $width) > ($this->height / $height)
                ? Enum::HEIGHT
                : Enum::WIDTH;
        }

        return $master;
    }

    /**
     * Parses a hex color ("#rgb", "rgb", "#rrggbb" or "rrggbb") into an array
     * of three integer channels [red, green, blue].
     *
     * @param string $color
     *
     * @return array
     * @throws InvalidColor
     */
    private function parseColor(string $color): array
    {
        if (
            strlen($color) > 1 &&
            substr($color, 0, 1) === "#"
        ) {
            $color = substr($color, 1);
        }

        if (strlen($color) === 3) {
            $color = preg_replace("/./", "$0$0", $color);
        }

        if (1 !== preg_match("/^[0-9a-fA-F]{6}$/", $color)) {
            throw new InvalidColor($color);
        }

        return array_map(
            "hexdec",
            str_split($color, 2)
        );
    }
}
