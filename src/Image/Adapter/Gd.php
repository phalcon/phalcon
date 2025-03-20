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
use Phalcon\Image\Enum;
use Phalcon\Image\Exception;

use function abs;
use function defined;
use function file_exists;
use function function_exists;
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
use function imagedestroy;
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
use function imagescale;
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

class Gd extends AbstractAdapter
{
    /**
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

        if (true === file_exists($this->file)) {
            $this->realpath = realpath($this->file);
            $imageInfo      = getimagesize($this->file);

            if (false !== $imageInfo) {
                $this->width  = $imageInfo[0];
                $this->height = $imageInfo[1];
                $this->type   = $imageInfo[2];
                $this->mime   = $imageInfo["mime"];
            }

            switch ($this->type) {
                case IMAGETYPE_GIF:
                    $this->image = imagecreatefromgif($this->file);
                    break;

                case IMAGETYPE_JPEG:
                case IMAGETYPE_JPEG2000:
                    $this->image = imagecreatefromjpeg($this->file);
                    break;

                case IMAGETYPE_PNG:
                    $this->image = imagecreatefrompng($this->file);
                    break;

                case IMAGETYPE_WEBP:
                    $this->image = imagecreatefromwebp($this->file);
                    break;

                case IMAGETYPE_WBMP:
                    $this->image = imagecreatefromwbmp($this->file);
                    break;

                case IMAGETYPE_XBM:
                    $this->image = imagecreatefromxbm($this->file);
                    break;

                default:
                    if ($this->mime) {
                        throw new Exception(
                            "Installed GD does not support " . $this->mime . " images"
                        );
                    }

                    throw new Exception(
                        "Installed GD does not support such images"
                    );
            }

            imagesavealpha($this->image, true);
        } else {
            if (null === $width || null === $height) {
                throw new Exception(
                    "Failed to create image from file " . $this->file
                );
            }

            $this->image = imagecreatetruecolor($width, $height);

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
        $image = $this->image;

        if (null !== $image) {
            imagedestroy($image);
        }
    }

    /**
     * @return string
     * @throws Exception
     */
    public function getVersion(): string
    {
        if (true !== function_exists("gd_info")) {
            throw new Exception(
                "GD is either not installed or not enabled, check your configuration"
            );
        }

        $version = null;

        if (defined("GD_VERSION")) {
            $version = GD_VERSION;
        } else {
            $info    = gd_info();
            $matches = null;

            if (
                preg_match(
                    "/\\d+\\.\\d+(?:\\.\\d+)?/",
                    $info["GD Version"],
                    $matches
                )
            ) {
                $version = $matches[0];
            }
        }

        return $version;
    }

    /**
     * @param int $red
     * @param int $green
     * @param int $blue
     * @param int $opacity
     *
     * @return void
     */
    protected function processBackground(
        int $red,
        int $green,
        int $blue,
        int $opacity
    ): void {
        $opacity    = (int)round(abs(($opacity * 127 / 100) - 127));
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
            imagedestroy($image);

            $this->image = $background;
        }
    }

    /**
     * @param int $radius
     *
     * @return void
     */
    protected function processBlur(int $radius): void
    {
        $counter = 0;
        while ($counter < $radius) {
            imagefilter($this->image, IMG_FILTER_GAUSSIAN_BLUR);

            $counter++;
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
        $image = imagecreatetruecolor($width, $height);

        imagealphablending($image, false);
        imagesavealpha($image, true);

        return $image;
    }

    /**
     * @param int $width
     * @param int $height
     * @param int $offsetX
     * @param int $offsetY
     *
     * @return void
     */
    protected function processCrop(
        int $width,
        int $height,
        int $offsetX,
        int $offsetY
    ): void {
        $rect = [
            "x"      => $offsetX,
            "y"      => $offsetY,
            "width"  => $width,
            "height" => $height,
        ];

        $image = imagecrop($this->image, $rect);

        imagedestroy($this->image);

        $this->image  = $image;
        $this->width  = imagesx($image);
        $this->height = imagesy($image);
    }

    /**
     * @param int $direction
     *
     * @return void
     */
    protected function processFlip(int $direction): void
    {
        if ($direction === Enum::HORIZONTAL) {
            imageflip($this->image, IMG_FLIP_HORIZONTAL);
        } else {
            imageflip($this->image, IMG_FLIP_VERTICAL);
        }
    }

    /**
     * @param AdapterInterface $mask
     *
     * @return void
     */
    protected function processMask(AdapterInterface $mask)
    {
        $maskImage  = imagecreatefromstring($mask->render());
        $maskWidth  = (int)imagesx($maskImage);
        $maskHeight = (int)imagesy($maskImage);
        $alpha      = 127;

        imagesavealpha($maskImage, true);

        $newImage = $this->processCreate($this->width, $this->height);

        imagesavealpha($newImage, true);

        $color = imagecolorallocatealpha(
            $newImage,
            0,
            0,
            0,
            $alpha
        );

        imagefill($newImage, 0, 0, $color);

        if ($this->width !== $maskWidth || $this->height !== $maskHeight) {
            $tempImage = imagecreatetruecolor($this->width, $this->height);

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

            imagedestroy($maskImage);

            $maskImage = $tempImage;
        }

        $x = 0;
        while ($x < $this->width) {
            $y = 0;
            while ($y < $this->height) {
                $index = imagecolorat($maskImage, $x, $y);
                $color = imagecolorsforindex($maskImage, $index);

                if (isset($color["red"])) {
                    $alpha = 127 - intval($color["red"] / 2);
                }

                $index = imagecolorat($this->image, $x, $y);
                $color = imagecolorsforindex($this->image, $index);
                $red   = $color["red"];
                $green = $color["green"];
                $blue  = $color["blue"];
                $color = imagecolorallocatealpha(
                    $newImage,
                    $red,
                    $green,
                    $blue,
                    $alpha
                );

                imagesetpixel($newImage, $x, $y, $color);

                $y++;
            }

            $x++;
        }

        imagedestroy($this->image);
        imagedestroy($maskImage);

        $this->image = $newImage;
    }

    /**
     * @param int $amount
     *
     * @return void
     */
    protected function processPixelate(int $amount): void
    {
        $x = 0;

        while ($x < $this->width) {
            $y = 0;

            while ($y < $this->height) {
                $x1 = (int)($x + ($amount / 2));
                $y1 = (int)($y + ($amount / 2));

                if ($x1 >= $this->width || $y1 >= $this->height) {
                    break;
                }

                $color = imagecolorat($this->image, $x1, $y1);
                $x2    = $x + $amount;
                $y2    = $y + $amount;

                imagefilledrectangle(
                    $this->image,
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

    /**
     * @param int  $height
     * @param int  $opacity
     * @param bool $fadeIn
     *
     * @return void
     */
    protected function processReflection(
        int $height,
        int $opacity,
        bool $fadeIn
    ): void {
        $opacity = (int)round(abs(($opacity * 127 / 100) - 127));

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
            $this->image,
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
                $this->image,
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

        imagedestroy($this->image);

        $this->image  = $reflection;
        $this->width  = imagesx($reflection);
        $this->height = imagesy($reflection);
    }

    /**
     * @param string $extension
     * @param int    $quality
     *
     * @return string
     * @throws Exception
     */
    protected function processRender(string $extension, int $quality)
    {
        $extension = strtolower($extension);

        ob_start();
        switch ($extension) {
            case "gif":
                imagegif($this->image);
                break;
            case "jpg":
            case "jpeg":
                imagejpeg($this->image, null, $quality);
                break;
            case "png":
                imagepng($this->image);
                break;
            case "wbmp":
                imagewbmp($this->image);
                break;
            case "webp":
                imagewebp($this->image);
                break;
            case "xbm":
                imagexbm($this->image, null);
                break;
            default:
                throw new Exception(
                    "Installed GD does not support '" . $extension . "' images"
                );
        }

        return (string)ob_get_clean();
    }

    /**
     * @param int $width
     * @param int $height
     *
     * @return void
     */
    protected function processResize(int $width, int $height): void
    {
        $image = imagescale($this->image, $width, $height);

        imagedestroy($this->image);

        $this->image  = $image;
        $this->width  = imagesx($image);
        $this->height = imagesy($image);
    }

    /**
     * @param int $degrees
     *
     * @return void
     */
    protected function processRotate(int $degrees): void
    {
        $transparent = imagecolorallocatealpha(
            $this->image,
            0,
            0,
            0,
            127
        );

        $image = imagerotate(
            $this->image,
            360 - $degrees,
            $transparent
        );

        imagesavealpha($image, true);

        $width  = imagesx($image);
        $height = imagesy($image);

        $copy = imagecopymerge(
            $this->image,
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
            imagedestroy($this->image);

            $this->image  = $image;
            $this->width  = $width;
            $this->height = $height;
        }
    }

    /**
     * @param string $file
     * @param int    $quality
     *
     * @return void
     * @throws Exception
     */
    protected function processSave(string $file, int $quality): void
    {
        /** @var string $extension */
        $extension = pathinfo($file, PATHINFO_EXTENSION);

        // If no extension is given, revert to the original type.
        if (empty($extension)) {
            $extension = image_type_to_extension($this->type, false);
        }

        $extension = strtolower($extension);
        switch ($extension) {
            case "gif":
                $this->type = IMAGETYPE_GIF;
                imagegif($this->image, $file);
                break;
            case "jpg":
            case "jpeg":
                $this->type = IMAGETYPE_JPEG;

                if ($quality >= 0) {
                    $quality = $this->checkHighLow($quality, 1);
                    imagejpeg($this->image, $file, $quality);
                } else {
                    imagejpeg($this->image, $file);
                }
                break;
            case "png":
                $this->type = IMAGETYPE_PNG;
                imagepng($this->image, $file);
                break;
            case "wbmp":
                $this->type = IMAGETYPE_WBMP;
                imagewbmp($this->image, $file);
                break;
            case "webp":
                $this->type = IMAGETYPE_WEBP;
                imagewebp($this->image, $file);
                break;
            case "xbm":
                $this->type = IMAGETYPE_XBM;
                imagexbm($this->image, $file);
                break;
            default:
                throw new Exception(
                    "Installed GD does not support '" . $extension . "' images"
                );
        }

        $this->mime = image_type_to_mime_type($this->type);
    }

    /**
     * @param int $amount
     *
     * @return void
     */
    protected function processSharpen(int $amount): void
    {
        $amount = (int)round(abs(-18 + ($amount * 0.08)), 2);

        $matrix = [
            [-1, -1, -1],
            [-1, $amount, -1],
            [-1, -1, -1],
        ];

        $result = imageconvolution(
            $this->image,
            $matrix,
            $amount - 8,
            0
        );
        if (true === $result) {
            $this->width  = imagesx($this->image);
            $this->height = imagesy($this->image);
        }
    }

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
        $bottomLeftX = 0;
        $bottomLeftY = 0;
        $topRightX   = 0;
        $topRightY   = 0;
        $offsetX     = (int)$offsetX;
        $offsetY     = (int)$offsetY;

        $opacity = (int)round(abs(($opacity * 127 / 100) - 127));

        if (!empty($fontFile)) {
            $space = imagettfbbox($size, 0, $fontFile, $text);

            if (false === $space) {
                throw new Exception("Call to imagettfbbox() failed");
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

            $color = imagecolorallocatealpha(
                $this->image,
                $red,
                $green,
                $blue,
                $opacity
            );

            $angle = 0;
            imagettftext(
                $this->image,
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

            $color = imagecolorallocatealpha(
                $this->image,
                $red,
                $green,
                $blue,
                $opacity
            );

            imagestring(
                $this->image,
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
        $overlay = imagecreatefromstring($watermark->render());

        imagesavealpha($overlay, true);

        $width  = (int)imagesx($overlay);
        $height = (int)imagesy($overlay);

        if ($opacity < 100) {
            $opacity = (int)round(
                abs(
                    ($opacity * 127 / 100) - 127
                )
            );

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

        imagealphablending($this->image, true);

        $copy = imagecopy(
            $this->image,
            $overlay,
            $offsetX,
            $offsetY,
            0,
            0,
            $width,
            $height
        );
        if (true === $copy) {
            imagedestroy($overlay);
        }
    }

    /**
     * Checks the installed version of GD
     *
     * @return void
     * @throws Exception
     */
    private function check(): void
    {
        $version = $this->getVersion();

        if (true !== version_compare($version, "2.0.1", ">=")) {
            throw new Exception(
                "Phalcon\\Image\\Adapter\\GD requires GD " .
                "version '2.0.1' or greater, you have " . $version
            );
        }
    }
}
