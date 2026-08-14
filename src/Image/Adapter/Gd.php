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
use Phalcon\Contracts\Image\ImageTypes;
use Phalcon\Image\Enum;
use Phalcon\Image\Exception;
use Phalcon\Image\Exceptions\ExtensionNotLoaded;
use Phalcon\Image\Exceptions\ImageLoadFailed;
use Phalcon\Image\Exceptions\TextRenderingFailed;
use Phalcon\Image\Exceptions\UnsupportedImageType;
use Phalcon\Image\Exceptions\VersionMismatch;
use Phalcon\Traits\Php\FileTrait;
use Phalcon\Traits\Php\InfoTrait;

use function abs;
use function defined;
use function gd_info;
use function getimagesize;
use function image_type_to_extension;
use function image_type_to_mime_type;
use function imagealphablending;
use function imagecolorallocatealpha;
use function imagecolorat;
use function imagecolorsforindex;
use function imageconvolution;
use function imagecopy;
use function imagecopymerge;
use function imagecopyresampled;
use function imagecreatefromgif;
use function imagecreatefromjpeg;
use function imagecreatefrompng;
use function imagecreatefromstring;
use function imagecreatefromwbmp;
use function imagecreatefromwebp;
use function imagecreatefromxbm;
use function imagecreatetruecolor;
use function imagecrop;
use function imagefill;
use function imagefilledrectangle;
use function imagefilter;
use function imageflip;
use function imagefontheight;
use function imagefontwidth;
use function imagegif;
use function imagejpeg;
use function imagelayereffect;
use function imagepng;
use function imagerotate;
use function imagesavealpha;
use function imagesetpixel;
use function imagestring;
use function imagesx;
use function imagesy;
use function imagettfbbox;
use function imagettftext;
use function imagewbmp;
use function imagewebp;
use function imagexbm;
use function intval;
use function ob_get_clean;
use function ob_start;
use function pathinfo;
use function preg_match;
use function realpath;
use function round;
use function strlen;
use function strtolower;
use function version_compare;

use const GD_VERSION;
use const IMAGETYPE_GIF;
use const IMAGETYPE_JPEG;
use const IMAGETYPE_JPEG2000;
use const IMAGETYPE_PNG;
use const IMAGETYPE_WBMP;
use const IMAGETYPE_WEBP;
use const IMAGETYPE_XBM;
use const IMG_EFFECT_OVERLAY;
use const IMG_FILTER_COLORIZE;
use const IMG_FILTER_GAUSSIAN_BLUR;
use const IMG_FLIP_HORIZONTAL;
use const IMG_FLIP_VERTICAL;
use const PATHINFO_EXTENSION;

/**
 * Image manipulation backed by the GD extension.
 *
 * Capabilities:
 *
 * | Aspect              | Support                                     |
 * |---------------------|---------------------------------------------|
 * | Load formats        | GIF, JPEG, JPEG 2000, PNG, WEBP, WBMP, XBM  |
 * | Render/save formats | GIF, JPEG, PNG, WBMP, WEBP, XBM             |
 * | Backend-only API    | none                                        |
 *
 * Unsupported render/save formats raise
 * Phalcon\Image\Exceptions\UnsupportedImageType. Visual semantics differ from
 * the Imagick adapter: blur() applies repeated 3x3 Gaussian convolutions
 * (the radius is the number of passes), while sharpen and reflection use GD's
 * own scales. Switching the factory backend can change the rendered output.
 *
 * @extends AbstractAdapter<GdImage>
 *
 * @phpstan-import-type image_crop_rectangle from ImageTypes
 * @phpstan-import-type image_text_bounds from ImageTypes
 */
class Gd extends AbstractAdapter
{
    use FileTrait;
    use InfoTrait;

    /**
     * Loads an image from a file, or creates a blank canvas.
     *
     * When the file exists it is loaded. When the file does not exist and both
     * a width and a height are supplied, a blank true-color canvas is created
     * instead - its realpath, mime and type then describe a PNG canvas rather
     * than the named file. Prefer Gd::create() for the canvas case; this dual
     * mode is slated for removal in the next major version.
     *
     * @param string   $file
     * @param int|null $width
     * @param int|null $height
     *
     * @throws Exception
     */
    public function __construct(
        string $file,
        int | null $width = null,
        int | null $height = null
    ) {
        $this->check();

        $this->file = $file;
        $this->type = 0;

        if (true === $this->phpFileExists($this->file)) {
            $this->realpath = (string)realpath($this->file);
            $imageInfo      = getimagesize($this->file);

            if (false !== $imageInfo) {
                $this->width  = $imageInfo[0];
                $this->height = $imageInfo[1];
                $this->type   = $imageInfo[2];
                $this->mime   = $imageInfo["mime"];
            } else {
                throw new ImageLoadFailed($this->file);
            }

            switch ($this->type) {
                case IMAGETYPE_GIF:
                    $image = imagecreatefromgif($this->file);
                    break;

                case IMAGETYPE_JPEG:
                case IMAGETYPE_JPEG2000:
                    $image = imagecreatefromjpeg($this->file);
                    break;

                case IMAGETYPE_PNG:
                    $image = imagecreatefrompng($this->file);
                    break;

                case IMAGETYPE_WEBP:
                    $image = imagecreatefromwebp($this->file);
                    break;

                case IMAGETYPE_WBMP:
                    $image = imagecreatefromwbmp($this->file);
                    break;

                case IMAGETYPE_XBM:
                    $image = imagecreatefromxbm($this->file);
                    break;

                default:
                    throw new UnsupportedImageType($this->mime);
            }

            /** @var GdImage $image */
            $this->image = $image;

            imagesavealpha($this->image, true);
        } else {
            if (null === $width || null === $height) {
                throw new ImageLoadFailed($this->file);
            }

            /**
             * @var positive-int $height
             * @var positive-int $width
             */
            $image = imagecreatetruecolor($width, $height);

            /** @var GdImage $image */
            $this->image = $image;

            imagealphablending($this->image, true);
            imagesavealpha($this->image, true);

            $this->realpath = $this->file;
            $this->width    = $width;
            $this->height   = $height;
            $this->type     = 3;
            $this->mime     = "image/png";
        }
    }

    /**
     * Destructor
     */
    public function __destruct()
    {
        $this->image = null;
    }

    /**
     * Creates a blank true-color canvas of the given dimensions, without the
     * load-or-create ambiguity of the constructor.
     *
     * @phpstan-return AbstractAdapter<GdImage>
     * @throws Exception
     */
    public static function create(int $width, int $height): AbstractAdapter
    {
        return new self("", $width, $height);
    }

    /**
     * @throws Exception
     */
    public function getVersion(): string
    {
        if (true !== $this->phpFunctionExists("gd_info")) {
            throw new ExtensionNotLoaded("GD");
        }

        $version = "";

        if (defined("GD_VERSION")) {
            $version = GD_VERSION;
        } else {
            $info    = gd_info();
            $matches = null;

            /** @var string $reported */
            $reported = $info["GD Version"];

            if (
                preg_match(
                    "/\\d+\\.\\d+(?:\\.\\d+)?/",
                    $reported,
                    $matches
                )
            ) {
                $version = $matches[0];
            }
        }

        return $version;
    }

    protected function processBackground(
        int $red,
        int $green,
        int $blue,
        int $opacity
    ): void {
        /** @var int<0, 127> $opacity */
        $opacity = (int)round(abs(($opacity * 127 / 100) - 127));

        /** @var GdImage $image */
        $image      = $this->image;
        $background = $this->processCreate($this->width, $this->height);

        $color = imagecolorallocatealpha(
            $background,
            $red,
            $green,
            $blue,
            $opacity
        );

        imagealphablending($background, true);

        $copy = imagecopy(
            $background,
            $image,
            0,
            0,
            0,
            0,
            $this->width,
            $this->height
        );
        if (false !== $copy) {
            $this->image = $background;
        }
    }

    protected function processBlur(int $radius): void
    {
        /** @var GdImage $image */
        $image = $this->image;

        $counter = 0;
        while ($counter < $radius) {
            imagefilter($image, IMG_FILTER_GAUSSIAN_BLUR);

            $counter++;
        }
    }

    /**
     * @phpstan-return GdImage
     */
    protected function processCreate(int $width, int $height)
    {
        /**
         * @var positive-int $height
         * @var positive-int $width
         */
        $image = imagecreatetruecolor($width, $height);

        /** @var GdImage $image */
        imagealphablending($image, false);
        imagesavealpha($image, true);

        return $image;
    }

    protected function processCrop(
        int $width,
        int $height,
        int $offsetX,
        int $offsetY
    ): void {
        /** @var image_crop_rectangle $rect */
        $rect = [
            "x"      => $offsetX,
            "y"      => $offsetY,
            "width"  => $width,
            "height" => $height,
        ];

        /** @var GdImage $current */
        $current = $this->image;

        /** @var GdImage $image */
        $image = imagecrop($current, $rect);

        $this->image  = $image;
        $this->width  = imagesx($image);
        $this->height = imagesy($image);
    }

    protected function processFlip(int $direction): void
    {
        /** @var GdImage $image */
        $image = $this->image;

        if ($direction === Enum::HORIZONTAL) {
            imageflip($image, IMG_FLIP_HORIZONTAL);
        } else {
            imageflip($image, IMG_FLIP_VERTICAL);
        }
    }

    protected function processMask(AdapterInterface $mask)
    {
        /** @var GdImage $maskImage */
        $maskImage = imagecreatefromstring($mask->render());

        /** @var GdImage $current */
        $current = $this->image;

        $maskWidth  = (int)imagesx($maskImage);
        $maskHeight = (int)imagesy($maskImage);

        /** @var int<0, 127> $alpha */
        $alpha = 127;

        imagesavealpha($maskImage, true);

        $newImage = $this->processCreate($this->width, $this->height);

        imagesavealpha($newImage, true);

        /** @var int<0, max> $color */
        $color = imagecolorallocatealpha(
            $newImage,
            0,
            0,
            0,
            $alpha
        );

        imagefill($newImage, 0, 0, $color);

        if ($this->width !== $maskWidth || $this->height !== $maskHeight) {
            /** @var positive-int $width */
            $width = $this->width;

            /** @var positive-int $height */
            $height = $this->height;

            /** @var GdImage $tempImage */
            $tempImage = imagecreatetruecolor($width, $height);

            imagecopyresampled(
                $tempImage,
                $maskImage,
                0,
                0,
                0,
                0,
                $this->width,
                $this->height,
                $maskWidth,
                $maskHeight
            );

            $maskImage = $tempImage;
        }

        $x = 0;
        while ($x < $this->width) {
            $y = 0;
            while ($y < $this->height) {
                /** @var int<0, max> $index */
                $index = imagecolorat($maskImage, $x, $y);
                $color = imagecolorsforindex($maskImage, $index);

                /** @var int<0, 127> $alpha */
                $alpha = 127 - intval($color["red"] / 2);

                /** @var int<0, max> $index */
                $index = imagecolorat($current, $x, $y);
                $color = imagecolorsforindex($current, $index);
                $red   = $color["red"];
                $green = $color["green"];
                $blue  = $color["blue"];

                /** @var int<0, max> $pixel */
                $pixel = imagecolorallocatealpha(
                    $newImage,
                    $red,
                    $green,
                    $blue,
                    $alpha
                );

                imagesetpixel($newImage, $x, $y, $pixel);

                $y++;
            }

            $x++;
        }

        $this->image = $newImage;
    }

    protected function processPixelate(int $amount): void
    {
        /** @var GdImage $image */
        $image = $this->image;

        $x = 0;

        while ($x < $this->width) {
            $y = 0;

            while ($y < $this->height) {
                $x1 = (int)($x + ($amount / 2));
                $y1 = (int)($y + ($amount / 2));

                if ($x1 >= $this->width || $y1 >= $this->height) {
                    break;
                }

                /** @var int<0, max> $color */
                $color = imagecolorat($image, $x1, $y1);
                $x2    = $x + $amount;
                $y2    = $y + $amount;

                imagefilledrectangle(
                    $image,
                    $x,
                    $y,
                    $x2,
                    $y2,
                    $color
                );

                $y += $amount;
            }

            $x += $amount;
        }
    }

    protected function processReflection(
        int $height,
        int $opacity,
        bool $fadeIn
    ): void {
        /** @var int<0, 127> $opacity */
        $opacity = (int)round(abs(($opacity * 127 / 100) - 127));

        /** @var GdImage $image */
        $image = $this->image;

        if ($opacity < 127) {
            $stepping = (127 - $opacity) / $height;
        } else {
            $stepping = 127 / $height;
        }

        $reflection = $this->processCreate(
            $this->width,
            $this->height + $height
        );

        imagecopy(
            $reflection,
            $image,
            0,
            0,
            0,
            0,
            $this->width,
            $this->height
        );

        $offset = 0;
        while ($height >= $offset) {
            $sourceY      = $this->height - $offset - 1;
            $destinationY = $this->height + $offset;

            if ($fadeIn) {
                $destinationOpacity = (int)round(
                    $opacity + ($stepping * ($height - $offset))
                );
            } else {
                $destinationOpacity = (int)round(
                    $opacity + ($stepping * $offset)
                );
            }

            $line = $this->processCreate($this->width, 1);

            imagecopy(
                $line,
                $image,
                0,
                0,
                0,
                $sourceY,
                $this->width,
                1
            );

            imagefilter(
                $line,
                IMG_FILTER_COLORIZE,
                0,
                0,
                0,
                $destinationOpacity
            );

            imagecopy(
                $reflection,
                $line,
                0,
                $destinationY,
                0,
                0,
                $this->width,
                1
            );

            $offset++;
        }

        $this->image  = $reflection;
        $this->width  = imagesx($reflection);
        $this->height = imagesy($reflection);
    }

    protected function processRender(string $extension, int $quality): false | string
    {
        /** @var GdImage $image */
        $image = $this->image;

        $extension = strtolower($extension);

        ob_start();
        switch ($extension) {
            case "gif":
                imagegif($image);
                break;
            case "jpg":
            case "jpeg":
                imagejpeg($image, null, $quality);
                break;
            case "png":
                imagepng($image);
                break;
            case "wbmp":
                imagewbmp($image);
                break;
            case "webp":
                imagewebp($image);
                break;
            case "xbm":
                imagexbm($image, null);
                break;
            default:
                throw new UnsupportedImageType($extension);
        }

        return ob_get_clean();
    }

    protected function processResize(int $width, int $height): void
    {
        /** @var GdImage $current */
        $current = $this->image;

        /**
         * @var positive-int $height
         * @var positive-int $width
         */
        $image = imagecreatetruecolor($width, $height);

        /** @var GdImage $image */
        imagealphablending($image, false);
        imagesavealpha($image, true);
        imagecopyresampled($image, $current, 0, 0, 0, 0, $width, $height, $this->width, $this->height);

        $this->image  = $image;
        $this->width  = imagesx($image);
        $this->height = imagesy($image);
    }

    protected function processRotate(int $degrees): void
    {
        /** @var GdImage $current */
        $current = $this->image;

        /** @var int<0, max> $transparent */
        $transparent = imagecolorallocatealpha(
            $current,
            0,
            0,
            0,
            127
        );

        /** @var GdImage $image */
        $image = imagerotate(
            $current,
            360 - $degrees,
            $transparent
        );

        imagesavealpha($image, true);

        $width  = imagesx($image);
        $height = imagesy($image);

        $copy = imagecopymerge(
            $current,
            $image,
            0,
            0,
            0,
            0,
            $width,
            $height,
            100
        );
        if (false !== $copy) {
            $this->image  = $image;
            $this->width  = $width;
            $this->height = $height;
        }
    }

    /**
     * @throws Exception
     */
    protected function processSave(string $file, int $quality): bool
    {
        /** @var GdImage $image */
        $image = $this->image;

        /** @var string $extension */
        $extension = pathinfo($file, PATHINFO_EXTENSION);

        // If no extension is given, revert to the original type.
        if (empty($extension)) {
            $extension = (string)image_type_to_extension($this->type, false);
        }

        $extension = strtolower($extension);
        switch ($extension) {
            case "gif":
                $this->type = IMAGETYPE_GIF;
                imagegif($image, $file);
                break;
            case "jpg":
            case "jpeg":
                $this->type = IMAGETYPE_JPEG;

                if ($quality >= 0) {
                    $quality = $this->checkHighLow($quality, 1);
                    imagejpeg($image, $file, $quality);
                } else {
                    imagejpeg($image, $file);
                }
                break;
            case "png":
                $this->type = IMAGETYPE_PNG;
                imagepng($image, $file);
                break;
            case "wbmp":
                $this->type = IMAGETYPE_WBMP;
                imagewbmp($image, $file);
                break;
            case "webp":
                $this->type = IMAGETYPE_WEBP;
                imagewebp($image, $file);
                break;
            case "xbm":
                $this->type = IMAGETYPE_XBM;
                imagexbm($image, $file);
                break;
            default:
                throw new UnsupportedImageType($extension);
        }

        $this->mime = image_type_to_mime_type($this->type);

        return true;
    }

    protected function processSharpen(int $amount): void
    {
        $amount = (int)round(abs(-18 + ($amount * 0.08)), 2);

        $matrix = [
            [-1, -1, -1],
            [-1, $amount, -1],
            [-1, -1, -1],
        ];

        /** @var GdImage $image */
        $image = $this->image;

        $result = imageconvolution(
            $image,
            $matrix,
            $amount - 8,
            0
        );
        if (true === $result) {
            $this->width  = imagesx($image);
            $this->height = imagesy($image);
        }
    }

    /**
     * @throws Exception
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
        /** @var GdImage $image */
        $image = $this->image;

        $bottomLeftX = 0;
        $bottomLeftY = 0;
        $topRightX   = 0;
        $topRightY   = 0;
        $offsetX     = (int)$offsetX;
        $offsetY     = (int)$offsetY;

        /** @var int<0, 127> $opacity */
        $opacity = (int)round(abs(($opacity * 127 / 100) - 127));

        if (!empty($fontFile)) {
            /** @var false|image_text_bounds $space */
            $space = imagettfbbox($size, 0, $fontFile, $text);

            if (false === $space) {
                throw new TextRenderingFailed();
            }

            if (isset($space[0])) {
                $bottomLeftX = (int)$space[0];
                $bottomLeftY = (int)$space[1];
                $topRightX   = (int)$space[4];
                $topRightY   = (int)$space[5];
            }

            $width  = abs($topRightX - $bottomLeftX) + 10;
            $height = abs($topRightY - $bottomLeftY) + 10;

            if ($offsetX < 0) {
                $offsetX = $this->width - $width + $offsetX;
            }

            if ($offsetY < 0) {
                $offsetY = $this->height - $height + $offsetY;
            }

            /** @var int<0, max> $color */
            $color = imagecolorallocatealpha(
                $image,
                $red,
                $green,
                $blue,
                $opacity
            );

            $angle = 0;
            imagettftext(
                $image,
                $size,
                $angle,
                $offsetX,
                $offsetY,
                $color,
                $fontFile,
                $text
            );
        } else {
            $width  = imagefontwidth($size) * strlen($text);
            $height = imagefontheight($size);

            if ($offsetX < 0) {
                $offsetX = $this->width - $width + $offsetX;
            }

            if ($offsetY < 0) {
                $offsetY = $this->height - $height + $offsetY;
            }

            /** @var int<0, max> $color */
            $color = imagecolorallocatealpha(
                $image,
                $red,
                $green,
                $blue,
                $opacity
            );

            imagestring(
                $image,
                $size,
                $offsetX,
                $offsetY,
                $text,
                $color
            );
        }
    }

    protected function processWatermark(
        AdapterInterface $watermark,
        int $offsetX,
        int $offsetY,
        int $opacity
    ): void {
        /** @var GdImage $overlay */
        $overlay = imagecreatefromstring($watermark->render());

        /** @var GdImage $image */
        $image = $this->image;

        imagesavealpha($overlay, true);

        $width  = (int)imagesx($overlay);
        $height = (int)imagesy($overlay);

        if ($opacity < 100) {
            /** @var int<0, 127> $opacity */
            $opacity = (int)round(
                abs(
                    ($opacity * 127 / 100) - 127
                )
            );

            /** @var int<0, max> $color */
            $color = imagecolorallocatealpha(
                $overlay,
                127,
                127,
                127,
                $opacity
            );

            imagelayereffect($overlay, IMG_EFFECT_OVERLAY);

            imagefilledrectangle($overlay, 0, 0, $width, $height, $color);
        }

        imagealphablending($image, true);

        $copy = imagecopy(
            $image,
            $overlay,
            $offsetX,
            $offsetY,
            0,
            0,
            $width,
            $height
        );
    }

    /**
     * Checks the installed version of GD
     *
     * @throws Exception
     */
    private function check(): void
    {
        $version = $this->getVersion();

        if (true !== version_compare($version, "2.0.1", ">=")) {
            throw new VersionMismatch($version);
        }
    }
}
