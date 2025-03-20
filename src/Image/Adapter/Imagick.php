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

use GdImage;
use Imagick as ImagickNative;
use ImagickDraw;
use ImagickDrawException;
use ImagickException;
use ImagickPixel;
use ImagickPixelException;
use Phalcon\Image\Enum;
use Phalcon\Image\Exception;

use function is_bool;
use function is_float;
use function is_int;
use function strtolower;

use const IMAGETYPE_GIF;

/**
 * Phalcon\Image\Adapter\Imagick
 *
 * Image manipulation support. Resize, rotate, crop etc.
 *
 *```php
 * $image = new \Phalcon\Image\Adapter\Imagick("upload/test.jpg");
 *
 * $image->resize(200, 200)->rotate(90)->crop(100, 100);
 *
 * if ($image->save()) {
 *     echo "success";
 * }
 *```
 */
class Imagick extends AbstractAdapter
{
    private const ALPHACHANNEL_SET   = 8;
    private const CHANNEL_ALPHA      = 8;
    private const COMPOSITE_DISSOLVE = 28;
    private const COMPOSITE_DSTIN    = 23;
    private const COMPOSITE_DSTOUT   = 24;
    private const COMPOSITE_OVER     = 40;
    private const COMPOSITE_SRC      = 48;
    private const COMPRESSION_JPEG   = 8;
    private const EVALUATE_MULTIPLY  = 7;
    private const GRAVITY_CENTER     = 5;
    private const GRAVITY_EAST       = 6;
    private const GRAVITY_NORTH      = 2;
    private const GRAVITY_NORTHEAST  = 3;
    private const GRAVITY_SOUTH      = 8;
    private const GRAVITY_SOUTHEAST  = 9;
    private const GRAVITY_WEST       = 4;
    private const IMAGICK_EXTNUM     = 30700;
    /**
     * @var int
     */
    protected int $version = 0;

    /**
     * Constructor
     *
     * @param string   $file
     * @param int|null $width
     * @param int|null $height
     *
     * @throws Exception
     * @throws ImagickException
     */
    public function __construct(
        string $file,
        int | null $width = null,
        int | null $height = null
    ) {
        $this->check();

        $this->file  = $file;
        $this->image = new ImagickNative();

        if (true === file_exists($this->file)) {
            $this->realpath = realpath($this->file);

            if (true !== $this->image->readImage($this->realpath)) {
                throw new Exception(
                    "Imagick::readImage " . $this->file . " failed"
                );
            }

            if (!$this->image->getImageAlphaChannel()) {
                $this->image->setImageAlphaChannel(self::ALPHACHANNEL_SET);
            }
            $this->type = $this->image->getImageType();

            /**
             * GIF
             */
            if ($this->image->getImageType() == IMAGETYPE_GIF) {
                $image = $this->image->coalesceImages();

                $this->image->clear();
                $this->image->destroy();

                $this->image = $image;
            }
        } else {
            if (null === $width || null === $height) {
                throw new Exception(
                    "Failed to create image from file " . $this->file
                );
            }

            $this->image->newImage(
                $width,
                $height,
                new ImagickPixel("transparent")
            );

            $this->image->setFormat("png");
            $this->image->setImageFormat("png");

            $this->realpath = $this->file;
        }

        $this->width  = $this->image->getImageWidth();
        $this->height = $this->image->getImageHeight();
        $this->type   = $this->image->getImageType();
        $this->mime   = "image/" . $this->image->getImageFormat();
    }

    /**
     * Destroys the loaded image to free up resources.
     */
    public function __destruct()
    {
        if ($this->image instanceof ImagickNative) {
            $this->image->clear();
            $this->image->destroy();
        }
    }

    /**
     * This method scales the images using liquid rescaling method. Only support
     * Imagick
     *
     * @param int $width    new width
     * @param int $height   new height
     * @param int $deltaX   How much the seam can traverse on x-axis. Passing
     *                      0 causes the seams to be straight.
     * @param int $rigidity Introduces a bias for non-straight seams. This
     *                      parameter is typically 0.
     *
     * @return void
     * @throws Exception
     * @throws ImagickException
     */
    public function liquidRescale(
        int $width,
        int $height,
        int $deltaX = 0,
        int $rigidity = 0
    ): void {
        $image = $this->image;

        $image->setIteratorIndex(0);

        while (true) {
            $return = $image->liquidRescaleImage(
                $width,
                $height,
                $deltaX,
                $rigidity
            );

            if ($return !== true) {
                throw new Exception("Imagick::liquidRescale failed");
            }

            if ($image->nextImage() === false) {
                break;
            }
        }

        $this->width  = $image->getImageWidth();
        $this->height = $image->getImageHeight();
    }

    /**
     * Sets the limit for a particular resource in megabytes
     *
     * @param int $type
     * @param int $limit
     *
     * @return void
     * @throws Exception
     * @throws ImagickException
     *
     * @link https://www.php.net/manual/en/imagick.constants.php#imagick.constants.resourcetypes
     */
    public function setResourceLimit(int $type, int $limit): void
    {
        /**
         * The constants are all integers and are 0-6
         */
        if ($type >= 0 && $type <= 6) {
            $this->image->setResourceLimit($type, $limit);
        } else {
            throw new Exception(
                "Cannot set the Resource Type for this image"
            );
        }
    }

    /**
     * Execute a background.
     *
     * @param int $red
     * @param int $green
     * @param int $blue
     * @param int $opacity
     *
     * @return void
     * @throws Exception
     * @throws ImagickException
     * @throws ImagickPixelException
     */
    protected function processBackground(
        int $red,
        int $green,
        int $blue,
        int $opacity
    ): void {
        $opacity    /= 100;
        $color      = sprintf("rgb(%d, %d, %d)", $red, $green, $blue);
        $pixel1     = new ImagickPixel($color);
        $pixel2     = new ImagickPixel("transparent");
        $background = new ImagickNative();

        $this->image->setIteratorIndex(0);

        while (true) {
            $background->newImage($this->width, $this->height, $pixel1);

            try {
                if (true !== $background->getImageAlphaChannel()) {
                    $background->setImageAlphaChannel(
                        self::ALPHACHANNEL_SET
                    );
                }
            } catch (ImagickException) {
                throw new Exception("Imagick::getImageAlphaChannel failed");
            }

            $background->setImageBackgroundColor($pixel2);

            $background->evaluateImage(
                self::EVALUATE_MULTIPLY,
                $opacity,
                self::CHANNEL_ALPHA
            );

            $background->setColorspace(
                $this->image->getColorspace()
            );

            $result = $background->compositeImage(
                $this->image,
                self::COMPOSITE_DISSOLVE,
                0,
                0
            );

            if (true !== $result) {
                throw new Exception("Imagick::compositeImage failed");
            }

            if (true !== $this->image->nextImage()) {
                break;
            }
        }

        $this->image->clear();
        $this->image->destroy();

        $this->image = $background;
    }

    /**
     * Blur image
     *
     * @param int $radius Blur radius
     *
     * @return void
     * @throws ImagickException
     */
    protected function processBlur(int $radius): void
    {
        $this->image->setIteratorIndex(0);

        while (true) {
            $this->image->blurImage($radius, 100);

            if (true !== $this->image->nextImage()) {
                break;
            }
        }
    }

    /**
     * @param int $width
     * @param int $height
     *
     * @return false|GdImage|resource
     */
    protected function processCreate(int $width, int $height)
    {
        return false;
    }

    /**
     * Execute a crop.
     *
     * @param int $width
     * @param int $height
     * @param int $offsetX
     * @param int $offsetY
     *
     * @return void
     * @throws ImagickException
     */
    protected function processCrop(
        int $width,
        int $height,
        int $offsetX,
        int $offsetY
    ): void {
        $image = $this->image;

        $image->setIteratorIndex(0);

        while (true) {
            $image->cropImage($width, $height, $offsetX, $offsetY);
            $image->setImagePage($width, $height, 0, 0);

            if (true !== $image->nextImage()) {
                break;
            }
        }

        $this->width  = $image->getImageWidth();
        $this->height = $image->getImageHeight();
    }

    /**
     * Execute a flip.
     *
     * @param int $direction
     *
     * @return void
     * @throws ImagickException
     */
    protected function processFlip(int $direction): void
    {
        $method = ($direction === Enum::HORIZONTAL) ? "flipImage" : "flopImage";

        $this->image->setIteratorIndex(0);

        while (true) {
            $this->image->$method();

            if (true !== $this->image->nextImage()) {
                break;
            }
        }
    }

    /**
     * Composite one image onto another
     *
     * @param AdapterInterface $image
     *
     * @return void
     * @throws Exception
     * @throws ImagickException
     */
    protected function processMask(AdapterInterface $image): void
    {
        $mask = new ImagickNative();

        $mask->readImageBlob($image->render());
        $this->image->setIteratorIndex(0);

        while (true) {
            $this->image->setImageMatte(true);

            $return = $this->image->compositeImage(
                $mask,
                self::COMPOSITE_DSTIN,
                0,
                0
            );

            if ($return !== true) {
                throw new Exception("Imagick::compositeImage failed");
            }

            if (true !== $this->image->nextImage()) {
                break;
            }
        }

        $mask->clear();
        $mask->destroy();
    }

    /**
     * Pixelate image
     *
     * @param int $amount amount to pixelate
     *
     * @return void
     * @throws ImagickException
     */
    protected function processPixelate(int $amount): void
    {
        $width  = $this->width / $amount;
        $height = $this->height / $amount;

        $this->image->setIteratorIndex(0);

        while (true) {
            $this->image->scaleImage($width, $height);
            $this->image->scaleImage($this->width, $this->height);

            if (true !== $this->image->nextImage()) {
                break;
            }
        }
    }

    /**
     * Execute a reflection.
     *
     * @param int  $height
     * @param int  $opacity
     * @param bool $fadeIn
     *
     * @return void
     * @throws Exception
     * @throws ImagickException
     */
    protected function processReflection(
        int $height,
        int $opacity,
        bool $fadeIn
    ): void {
        if ($this->version >= 30100) {
            $reflection = clone $this->image;
        } else {
            $reflection = clone $this->image->clone();
        }

        $reflection->setIteratorIndex(0);

        while (true) {
            $reflection->flipImage();

            $reflection->cropImage(
                $reflection->getImageWidth(),
                $height,
                0,
                0
            );

            $reflection->setImagePage(
                $reflection->getImageWidth(),
                $height,
                0,
                0
            );

            if (true !== $this->image->nextImage()) {
                break;
            }
        }

        $pseudo = $fadeIn ? "gradient:black-transparent" : "gradient:transparent-black";
        $fade   = new ImagickNative();

        $fade->newPseudoImage(
            $reflection->getImageWidth(),
            $reflection->getImageHeight(),
            $pseudo
        );

        $opacity /= 100;
        $reflection->setIteratorIndex(0);

        while (true) {
            $return = $reflection->compositeImage(
                $fade,
                self::COMPOSITE_DSTOUT,
                0,
                0
            );

            if ($return !== true) {
                throw new Exception("Imagick::compositeImage failed");
            }

            $reflection->evaluateImage(
                self::EVALUATE_MULTIPLY,
                $opacity,
                self::CHANNEL_ALPHA
            );

            if (true !== $this->image->nextImage()) {
                break;
            }
        }

        $fade->destroy();

        $image  = new ImagickNative();
        $pixel  = new ImagickPixel();
        $height = $this->image->getImageHeight() + $height;

        $this->image->setIteratorIndex(0);

        while (true) {
            $image->newImage($this->width, $height, $pixel);
            $image->setImageAlphaChannel(self::ALPHACHANNEL_SET);

            $image->setColorspace($this->image->getColorspace());
            $image->setImageDelay($this->image->getImageDelay());
            $return = $image->compositeImage(
                $this->image,
                self::COMPOSITE_SRC,
                0,
                0
            );

            if ($return !== true) {
                throw new Exception("Imagick::compositeImage failed");
            }

            if (true !== $this->image->nextImage()) {
                break;
            }
        }

        $image->setIteratorIndex(0);
        $reflection->setIteratorIndex(0);

        while (true) {
            $return = $image->compositeImage(
                $reflection,
                self::COMPOSITE_OVER,
                0,
                $this->height
            );

            if ($return !== true) {
                throw new Exception("Imagick::compositeImage failed");
            }

            if (true !== $image->nextImage() || true !== $reflection->nextImage()) {
                break;
            }
        }

        $reflection->destroy();

        $this->image->clear();
        $this->image->destroy();

        $this->image  = $image;
        $this->width  = $this->image->getImageWidth();
        $this->height = $this->image->getImageHeight();
    }

    /**
     * Execute a render.
     *
     * @param string $extension
     * @param int    $quality
     *
     * @return string
     * @throws ImagickException
     */
    protected function processRender(string $extension, int $quality): string
    {
        $image = $this->image;

        $image->setFormat($extension);
        $image->setImageFormat($extension);
        $image->stripImage();

        $this->type = $image->getImageType();
        $this->mime = "image/" . $image->getImageFormat();

        $extension = strtolower($extension);
        switch ($extension) {
            case "gif":
                $image->optimizeImageLayers();
                break;
            case "jpg":
            case "jpeg":
                $image->setImageCompression(self::COMPRESSION_JPEG);
                $image->setImageCompressionQuality($quality);
        }

        return $image->getImageBlob();
    }

    /**
     * Execute a resize.
     *
     * @param int $width
     * @param int $height
     *
     * @return void
     * @throws ImagickException
     */
    protected function processResize(int $width, int $height): void
    {
        $image = $this->image;
        $image->setIteratorIndex(0);

        while (true) {
            $image->scaleImage($width, $height);

            if (true !== $image->nextImage()) {
                break;
            }
        }

        $this->width  = $image->getImageWidth();
        $this->height = $image->getImageHeight();
    }

    /**
     * Execute a rotation.
     *
     * @param int $degrees
     *
     * @return void
     * @throws ImagickException
     */
    protected function processRotate(int $degrees): void
    {
        $this->image->setIteratorIndex(0);

        $pixel = new ImagickPixel();

        while (true) {
            $this->image->rotateImage($pixel, $degrees);

            $this->image->setImagePage(
                $this->width,
                $this->height,
                0,
                0
            );

            if (true !== $this->image->nextImage()) {
                break;
            }
        }

        $this->width  = $this->image->getImageWidth();
        $this->height = $this->image->getImageHeight();
    }

    /**
     * Execute a save.
     *
     * @param string $file
     * @param int    $quality
     *
     * @return void
     * @throws ImagickException
     */
    protected function processSave(string $file, int $quality): void
    {
        /** @var string $extension */
        $extension = pathinfo($file, PATHINFO_EXTENSION);

        $this->image->setFormat($extension);
        $this->image->setImageFormat($extension);

        $this->type = $this->image->getImageType();
        $this->mime = "image/" . $this->image->getImageFormat();

        $extension = strtolower($extension);
        switch ($extension) {
            case "gif":
                $this->image->optimizeImageLayers();
                $fp = fopen($file, "w");

                $this->image->writeImagesFile($fp);

                fclose($fp);

                return;
            case "jpg":
            case "jpeg":
                $this->image->setImageCompression(self::COMPRESSION_JPEG);
        }

        if ($quality >= 0) {
            $quality = $this->checkHighLow($quality, 1);
            $this->image->setImageCompressionQuality($quality);
        }

        $this->image->writeImage($file);
    }

    /**
     * Execute a sharpen.
     *
     * @param int $amount
     *
     * @return void
     * @throws ImagickException
     */
    protected function processSharpen(int $amount): void
    {
        $amount = ($amount < 5) ? 5 : $amount;
        $amount = ($amount * 3.0) / 100;

        $this->image->setIteratorIndex(0);

        while (true) {
            $this->image->sharpenImage(0, $amount);

            if (true !== $this->image->nextImage()) {
                break;
            }
        }
    }

    /**
     * Execute a text
     *
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
     * @throws ImagickDrawException
     * @throws ImagickException
     * @throws ImagickPixelException
     */
    protected function processText(
        string $text,
        mixed $offsetX,
        mixed $offsetY,
        int $opacity,
        int $red,
        int $green,
        int $blue,
        int $size,
        string | null $fontFile = null
    ): void {
        $opacity = $opacity / 100;
        $draw    = new ImagickDraw();
        $color   = sprintf("rgb(%d, %d, %d)", $red, $green, $blue);

        $draw->setFillColor(new ImagickPixel($color));

        if (null !== $fontFile) {
            $draw->setFont($fontFile);
        }

        if ($size) {
            $draw->setFontSize($size);
        }

        if ($opacity) {
            $draw->setfillopacity($opacity);
        }

        $gravity = null;
        if (is_bool($offsetX)) {
            if (is_bool($offsetY)) {
                $offsetX = 0;
                $offsetY = 0;
                $gravity = self::GRAVITY_CENTER;
            } elseif (is_int($offsetY)) {
                $y = $offsetY;

                $gravity = (true === $offsetX && $y < 0) ? self::GRAVITY_SOUTHEAST : $gravity;
                $gravity = (true === $offsetX && $y >= 0) ? self::GRAVITY_NORTHEAST : $gravity;
                $gravity = (true !== $offsetX && $y < 0) ? self::GRAVITY_SOUTH : $gravity;
                $gravity = (true !== $offsetX && $y >= 0) ? self::GRAVITY_NORTH : $gravity;

                $offsetX = 0;
                $offsetY = ($y < 0) ? $y * -1 : $offsetY;
            }
        } elseif (is_int($offsetX)) {
            $x = $offsetX;

            if ($offsetX) {
                if (is_bool($offsetY)) {
                    $gravity = (true === $offsetY && $x < 0) ? self::GRAVITY_SOUTHEAST : $gravity;
                    $gravity = (true === $offsetY && $x >= 0) ? self::GRAVITY_SOUTH : $gravity;
                    $gravity = (true !== $offsetY && $x < 0) ? self::GRAVITY_EAST : $gravity;
                    $gravity = (true !== $offsetY && $x >= 0) ? self::GRAVITY_WEST : $gravity;

                    $offsetY = 0;
                    $offsetX = ($x < 0) ? $x * -1 : $offsetX;
                } elseif (is_float($offsetY)) {
                    $y = (int)$offsetY;

                    $offsetX = ($x < 0) ? $x * -1 : 0;
                    $offsetY = ($y < 0) ? $y * -1 : $offsetY;

                    $gravity = ($y < 0) ? self::GRAVITY_SOUTHEAST : $gravity;
                    $gravity = ($y >= 0) ? self::GRAVITY_NORTHEAST : $gravity;
                }
            }
        }

        if (null !== $gravity) {
            $draw->setGravity($gravity);
        }

        $this->image->setIteratorIndex(0);

        while (true) {
            $this->image->annotateImage($draw, $offsetX, $offsetY, 0, $text);

            if (true !== $this->image->nextImage()) {
                break;
            }
        }

        $draw->destroy();
    }

    /**
     * Add Watermark
     *
     * @param AdapterInterface $image
     * @param int              $offsetX
     * @param int              $offsetY
     * @param int              $opacity
     *
     * @return void
     * @throws Exception
     * @throws ImagickException
     */
    protected function processWatermark(
        AdapterInterface $image,
        int $offsetX,
        int $offsetY,
        int $opacity
    ): void {
        $opacity   = $opacity / 100;
        $watermark = new ImagickNative();

        $watermark->readImageBlob($image->render());
        $watermark->evaluateImage(
            self::EVALUATE_MULTIPLY,
            $opacity,
            self::CHANNEL_ALPHA
        );

        $this->image->setIteratorIndex(0);

        while (true) {
            $return = $this->image->compositeImage(
                $watermark,
                self::COMPOSITE_OVER,
                $offsetX,
                $offsetY
            );

            if ($return !== true) {
                throw new Exception("Imagick::compositeImage failed");
            }

            if (true !== $this->image->nextImage()) {
                break;
            }
        }

        $watermark->clear();
        $watermark->destroy();
    }

    /**
     * Checks if Imagick is enabled
     *
     * @return void
     * @throws Exception
     */
    private function check(): void
    {
        if (true !== class_exists("imagick")) {
            throw new Exception(
                "Imagick is not installed, or the extension is not loaded"
            );
        }

        if (defined("Imagick::IMAGICK_EXTNUM")) {
            $this->version = self::IMAGICK_EXTNUM;
        }
    }
}
