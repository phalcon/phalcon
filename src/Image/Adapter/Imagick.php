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

use Imagick as ImagickNative;
use ImagickDraw;
use ImagickDrawException;
use ImagickException;
use ImagickPixel;
use ImagickPixelException;
use Phalcon\Image\Enum;
use Phalcon\Image\Exception;
use Phalcon\Image\Exceptions\CompositeFailed;
use Phalcon\Image\Exceptions\ExtensionNotLoaded;
use Phalcon\Image\Exceptions\ImageLoadFailed;
use Phalcon\Image\Exceptions\ResizeFailed;
use Phalcon\Image\Exceptions\ResourceTypeError;
use Phalcon\Traits\Php\FileTrait;

use function is_bool;
use function is_int;
use function strtolower;
use function strtoupper;

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
 *
 * Capabilities:
 *
 * | Aspect              | Support                                        |
 * |---------------------|------------------------------------------------|
 * | Load formats        | Whatever the linked ImageMagick build supports |
 * | Render/save formats | Whatever the linked ImageMagick build supports |
 * | Backend-only API    | liquidRescale(), setResourceLimit()            |
 *
 * Visual semantics differ from the Gd adapter: blur() maps the radius to a
 * blur sigma, while sharpen and reflection use ImageMagick's own scales.
 * Switching the factory backend can change the rendered output.
 *
 * @extends AbstractAdapter<ImagickNative>
 */
class Imagick extends AbstractAdapter
{
    use FileTrait;

    protected int $version = 0;

    /**
     * Loads an image from a file, or creates a blank canvas.
     *
     * When the file exists it is loaded. When the file does not exist and both
     * a width and a height are supplied, a blank transparent canvas is created
     * instead - its realpath, mime and type then describe a PNG canvas rather
     * than the named file. Prefer Imagick::create() for the canvas case; this
     * dual mode is slated for removal in the next major version.
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
        int | null $height = null,
        int $maxPixels = 0
    ) {
        $this->check();

        $this->file      = $file;
        $this->image     = new ImagickNative();
        $this->maxPixels = $maxPixels > 0 ? $maxPixels : self::DEFAULT_MAX_PIXELS;

        if (true === $this->phpFileExists($this->file)) {
            $this->realpath = (string)realpath($this->file);

            /**
             * Read only the header first and reject an oversized image by its
             * dimensions before readImage() decodes the full pixel buffer
             * (CWE-409).
             */
            if (true === $this->image->pingImage($this->realpath)) {
                $this->assertPixelLimit(
                    (int)$this->image->getImageWidth(),
                    (int)$this->image->getImageHeight()
                );

                $this->image->clear();
            }

            if (true !== $this->image->readImage($this->realpath)) {
                throw new ImageLoadFailed($this->file);
            }

            if (!$this->image->getImageAlphaChannel()) {
                $this->image->setImageAlphaChannel(ImagickNative::ALPHACHANNEL_SET);
            }
            $this->type = $this->image->getImageType();

            /**
             * GIF. The format, not the image type: getImageType() reports an
             * Imagick IMGTYPE_* value, which never equals an IMAGETYPE_* one.
             */
            if ("GIF" === strtoupper($this->image->getImageFormat())) {
                $image = $this->image->coalesceImages();

                $this->image->clear();
                $this->image->destroy();

                $this->image = $image;
            }
        } else {
            if (null === $width || null === $height) {
                throw new ImageLoadFailed($this->file);
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
     * Creates a blank transparent canvas of the given dimensions, without the
     * load-or-create ambiguity of the constructor.
     *
     * @phpstan-return AbstractAdapter<ImagickNative>
     * @throws Exception
     * @throws ImagickException
     */
    public static function create(int $width, int $height): AbstractAdapter
    {
        return new self("", $width, $height);
    }

    /**
     * This method scales the images using liquid rescaling method. Only support
     * Imagick
     *
     * @phpstan-return AbstractAdapter<ImagickNative>
     * @throws Exception
     * @throws ImagickException
     */
    public function liquidRescale(
        int $width,
        int $height,
        int $deltaX = 0,
        int $rigidity = 0
    ): AbstractAdapter {
        /** @var ImagickNative $image */
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
                throw new ResizeFailed();
            }

            if ($image->nextImage() === false) {
                break;
            }
        }

        $this->width  = $image->getImageWidth();
        $this->height = $image->getImageHeight();

        return $this;
    }

    /**
     * Sets the limit for a particular resource in megabytes
     *
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
        /** @var ImagickNative $image */
        $image = $this->image;

        if ($type >= 0 && $type <= 6) {
            $image->setResourceLimit($type, $limit);
        } else {
            throw new ResourceTypeError();
        }
    }

    /**
     * Execute a background.
     *
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
        $localOpacity = (float)$opacity / 100;
        $color        = sprintf("rgb(%d, %d, %d)", $red, $green, $blue);
        $pixel1       = new ImagickPixel($color);
        $pixel2       = new ImagickPixel("transparent");
        $background   = new ImagickNative();

        /** @var ImagickNative $image */
        $image = $this->image;

        $image->setIteratorIndex(0);

        while (true) {
            $background->newImage($this->width, $this->height, $pixel1);

            try {
                if (true !== $background->getImageAlphaChannel()) {
                    $background->setImageAlphaChannel(
                        ImagickNative::ALPHACHANNEL_SET
                    );
                }
            } catch (ImagickException) {
                throw new Exception("Imagick::getImageAlphaChannel failed");
            }

            $background->setImageBackgroundColor($pixel2);

            $background->evaluateImage(
                ImagickNative::EVALUATE_MULTIPLY,
                $localOpacity,
                ImagickNative::CHANNEL_ALPHA
            );

            $background->setColorspace(
                $image->getColorspace()
            );

            $result = $background->compositeImage(
                $image,
                ImagickNative::COMPOSITE_DISSOLVE,
                0,
                0
            );

            if (true !== $result) {
                throw new CompositeFailed();
            }

            if (true !== $image->nextImage()) {
                break;
            }
        }

        $image->clear();
        $image->destroy();

        $this->image = $background;
    }

    /**
     * Blur image
     *
     * @throws ImagickException
     */
    protected function processBlur(int $radius): void
    {
        /** @var ImagickNative $image */
        $image = $this->image;

        $image->setIteratorIndex(0);

        while (true) {
            $image->blurImage($radius, 100);

            if (true !== $image->nextImage()) {
                break;
            }
        }
    }

    /**
     * Execute a crop.
     *
     * @throws ImagickException
     */
    protected function processCrop(
        int $width,
        int $height,
        int $offsetX,
        int $offsetY
    ): void {
        /** @var ImagickNative $image */
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
     * @throws ImagickException
     */
    protected function processFlip(int $direction): void
    {
        $method = ($direction === Enum::HORIZONTAL) ? "flipImage" : "flopImage";

        /** @var ImagickNative $image */
        $image = $this->image;

        $image->setIteratorIndex(0);

        while (true) {
            $image->$method();

            if (true !== $image->nextImage()) {
                break;
            }
        }
    }

    /**
     * Composite one image onto another
     *
     * @throws Exception
     * @throws ImagickException
     */
    protected function processMask(AdapterInterface $mask): void
    {
        $image = new ImagickNative();

        /** @var ImagickNative $current */
        $current = $this->image;

        $image->readImageBlob($mask->render());
        $current->setIteratorIndex(0);

        while (true) {
            $current->setImageMatte(true);

            $return = $current->compositeImage(
                $image,
                ImagickNative::COMPOSITE_DSTIN,
                0,
                0
            );

            if ($return !== true) {
                throw new CompositeFailed();
            }

            if (true !== $current->nextImage()) {
                break;
            }
        }

        $image->clear();
        $image->destroy();
    }

    /**
     * Pixelate image
     *
     * @throws ImagickException
     */
    protected function processPixelate(int $amount): void
    {
        $width  = (int) ($this->width / $amount);
        $height = (int) ($this->height / $amount);

        /** @var ImagickNative $image */
        $image = $this->image;

        $image->setIteratorIndex(0);

        while (true) {
            $image->scaleImage($width, $height);
            $image->scaleImage($this->width, $this->height);

            if (true !== $image->nextImage()) {
                break;
            }
        }
    }

    /**
     * Execute a reflection.
     *
     * @throws Exception
     * @throws ImagickException
     */
    protected function processReflection(
        int $height,
        int $opacity,
        bool $fadeIn
    ): void {
        /** @var ImagickNative $current */
        $current = $this->image;

        if ($this->version >= 30100) {
            $reflection = clone $current;
        } else {
            $reflection = clone $current->clone();
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

            if (true !== $reflection->nextImage()) {
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

        $fadeOpacity = (float)$opacity / 100;
        $reflection->setIteratorIndex(0);

        while (true) {
            $return = $reflection->compositeImage(
                $fade,
                ImagickNative::COMPOSITE_DSTOUT,
                0,
                0
            );

            if ($return !== true) {
                throw new CompositeFailed();
            }

            $reflection->evaluateImage(
                ImagickNative::EVALUATE_MULTIPLY,
                $fadeOpacity,
                ImagickNative::CHANNEL_ALPHA
            );

            if (true !== $reflection->nextImage()) {
                break;
            }
        }

        $fade->destroy();

        $image = new ImagickNative();
        $pixel = new ImagickPixel();

        $current->setIteratorIndex(0);

        $height = $current->getImageHeight() + $height;

        while (true) {
            $image->newImage($this->width, $height, $pixel);
            $image->setImageAlphaChannel(ImagickNative::ALPHACHANNEL_SET);

            $image->setColorspace($current->getColorspace());
            $image->setImageDelay($current->getImageDelay());
            $return = $image->compositeImage(
                $current,
                ImagickNative::COMPOSITE_SRC,
                0,
                0
            );

            if ($return !== true) {
                throw new CompositeFailed();
            }

            if (true !== $current->nextImage()) {
                break;
            }
        }

        $image->setIteratorIndex(0);
        $reflection->setIteratorIndex(0);

        while (true) {
            $return = $image->compositeImage(
                $reflection,
                ImagickNative::COMPOSITE_OVER,
                0,
                $this->height
            );

            if ($return !== true) {
                throw new CompositeFailed();
            }

            if (true !== $image->nextImage() || true !== $reflection->nextImage()) {
                break;
            }
        }

        $reflection->destroy();

        $current->clear();
        $current->destroy();

        $this->image  = $image;
        $this->width  = $image->getImageWidth();
        $this->height = $image->getImageHeight();
    }

    /**
     * Execute a render.
     *
     * @throws ImagickException
     */
    protected function processRender(string $extension, int $quality): string
    {
        /** @var ImagickNative $image */
        $image = $this->image;

        $image->setFormat($extension);
        $image->setImageFormat($extension);
        $image->stripImage();

        $this->type = $image->getImageType();
        $this->mime = "image/" . $image->getImageFormat();

        $extension = strtolower($extension);
        switch ($extension) {
            case "gif":
                $this->setFramesFormat($image, $extension);

                $image->optimizeImageLayers();

                /**
                 * A blob of the current frame alone loses the animation
                 */
                return $image->getImagesBlob();
            case "jpg":
            case "jpeg":
                $image->setImageCompression(ImagickNative::COMPRESSION_JPEG);
                $image->setImageCompressionQuality($quality);
        }

        return $image->getImageBlob();
    }

    /**
     * Execute a resize.
     *
     * @throws ImagickException
     */
    protected function processResize(int $width, int $height): void
    {
        /** @var ImagickNative $image */
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
     * @throws ImagickException
     */
    protected function processRotate(int $degrees): void
    {
        /** @var ImagickNative $image */
        $image = $this->image;

        $image->setIteratorIndex(0);

        $pixel = new ImagickPixel();

        while (true) {
            $image->rotateImage($pixel, $degrees);

            $image->setImagePage(
                $this->width,
                $this->height,
                0,
                0
            );

            if (true !== $image->nextImage()) {
                break;
            }
        }

        $this->width  = $image->getImageWidth();
        $this->height = $image->getImageHeight();
    }

    /**
     * Execute a save.
     *
     * @throws ImagickException
     */
    protected function processSave(string $file, int $quality): bool
    {
        /** @var ImagickNative $image */
        $image = $this->image;

        /** @var string $extension */
        $extension = pathinfo($file, PATHINFO_EXTENSION);

        $image->setFormat($extension);
        $image->setImageFormat($extension);

        $this->type = $image->getImageType();
        $this->mime = "image/" . $image->getImageFormat();

        $extension = strtolower($extension);
        switch ($extension) {
            case "gif":
                $this->setFramesFormat($image, $extension);

                $image->optimizeImageLayers();

                /** @var resource $fp */
                $fp = fopen($file, "w");

                $image->writeImagesFile($fp);

                fclose($fp);

                return true;
            case "jpg":
            case "jpeg":
                $image->setImageCompression(ImagickNative::COMPRESSION_JPEG);
        }

        if ($quality >= 0) {
            $quality = $this->checkHighLow($quality, 1);
            $image->setImageCompressionQuality($quality);
        }

        $image->writeImage($file);

        return true;
    }

    /**
     * Execute a sharpen.
     *
     * @throws ImagickException
     */
    protected function processSharpen(int $amount): void
    {
        $amount = ($amount < 5) ? 5 : $amount;
        $sigma  = (float)$amount * 3.0 / 100;

        /** @var ImagickNative $image */
        $image = $this->image;

        $image->setIteratorIndex(0);

        while (true) {
            $image->sharpenImage(0, $sigma);

            if (true !== $image->nextImage()) {
                break;
            }
        }
    }

    /**
     * Execute a text
     *
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
        $textOpacity = (float)$opacity / 100;
        $draw        = new ImagickDraw();
        $color       = sprintf("rgb(%d, %d, %d)", $red, $green, $blue);

        $draw->setFillColor(new ImagickPixel($color));

        if (null !== $fontFile) {
            $draw->setFont($fontFile);
        }

        if ($size) {
            $draw->setFontSize($size);
        }

        if ($textOpacity) {
            $draw->setfillopacity($textOpacity);
        }

        $gravity = null;
        if (is_bool($offsetX)) {
            if (is_bool($offsetY)) {
                $offsetX = 0;
                $offsetY = 0;
                $gravity = ImagickNative::GRAVITY_CENTER;
            } elseif (is_int($offsetY)) {
                $y = $offsetY;

                $gravity = (true === $offsetX && $y < 0) ? ImagickNative::GRAVITY_SOUTHEAST : $gravity;
                $gravity = (true === $offsetX && $y >= 0) ? ImagickNative::GRAVITY_NORTHEAST : $gravity;
                $gravity = (true !== $offsetX && $y < 0) ? ImagickNative::GRAVITY_SOUTH : $gravity;
                $gravity = (true !== $offsetX && $y >= 0) ? ImagickNative::GRAVITY_NORTH : $gravity;

                $offsetX = 0;
                $offsetY = ($y < 0) ? $y * -1 : $offsetY;
            }
        } elseif (is_int($offsetX)) {
            $x = $offsetX;

            if ($offsetX) {
                if (is_bool($offsetY)) {
                    $gravity = (true === $offsetY && $x < 0) ? ImagickNative::GRAVITY_SOUTHEAST : $gravity;
                    $gravity = (true === $offsetY && $x >= 0) ? ImagickNative::GRAVITY_SOUTH : $gravity;
                    $gravity = (true !== $offsetY && $x < 0) ? ImagickNative::GRAVITY_EAST : $gravity;
                    $gravity = (true !== $offsetY && $x >= 0) ? ImagickNative::GRAVITY_WEST : $gravity;

                    $offsetY = 0;
                    $offsetX = ($x < 0) ? $x * -1 : $offsetX;
                } elseif (is_int($offsetY)) {
                    $y = $offsetY;

                    $offsetX = ($x < 0) ? $x * -1 : 0;
                    $offsetY = ($y < 0) ? $y * -1 : $offsetY;

                    $gravity = ($y < 0) ? ImagickNative::GRAVITY_SOUTHEAST : $gravity;
                    $gravity = ($y >= 0) ? ImagickNative::GRAVITY_NORTHEAST : $gravity;
                }
            }
        }

        if (null !== $gravity) {
            $draw->setGravity($gravity);
        }

        /**
         * The branches above leave the offsets untouched when offsetX is the
         * integer 0, so normalize them to the integers annotateImage() needs.
         */
        $offsetX = (int)$offsetX;
        $offsetY = (int)$offsetY;

        /** @var ImagickNative $image */
        $image = $this->image;

        $image->setIteratorIndex(0);

        while (true) {
            $image->annotateImage($draw, $offsetX, $offsetY, 0, $text);

            if (true !== $image->nextImage()) {
                break;
            }
        }

        $draw->destroy();
    }

    /**
     * Add Watermark
     *
     * @throws Exception
     * @throws ImagickException
     */
    protected function processWatermark(
        AdapterInterface $watermark,
        int $offsetX,
        int $offsetY,
        int $opacity
    ): void {
        $watermarkOpacity = (float)$opacity / 100;
        $image            = new ImagickNative();

        $image->readImageBlob($watermark->render());
        $image->evaluateImage(
            ImagickNative::EVALUATE_MULTIPLY,
            $watermarkOpacity,
            ImagickNative::CHANNEL_ALPHA
        );

        /** @var ImagickNative $current */
        $current = $this->image;

        $current->setIteratorIndex(0);

        while (true) {
            $return = $current->compositeImage(
                $image,
                ImagickNative::COMPOSITE_OVER,
                $offsetX,
                $offsetY
            );

            if ($return !== true) {
                throw new CompositeFailed();
            }

            if (true !== $current->nextImage()) {
                break;
            }
        }

        $image->clear();
        $image->destroy();
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
            throw new ExtensionNotLoaded("Imagick");
        }

        if (defined("Imagick::IMAGICK_EXTNUM")) {
            $this->version = ImagickNative::IMAGICK_EXTNUM;
        }
    }

    /**
     * Marks every frame with the format.
     *
     * setImageFormat() marks the current frame only, and a wand built with
     * newImage() carries no format at all, which stops a multi frame write.
     *
     * @param ImagickNative $image
     * @param string        $extension
     *
     * @return void
     * @throws ImagickException
     */
    private function setFramesFormat(ImagickNative $image, string $extension): void
    {
        $image->setIteratorIndex(0);

        while (true) {
            $image->setImageFormat($extension);

            if (true !== $image->nextImage()) {
                break;
            }
        }

        $image->setFormat($extension);
    }
}
