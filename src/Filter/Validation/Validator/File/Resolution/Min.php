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

namespace Phalcon\Filter\Validation\Validator\File\Resolution;

/**
 * Checks if a file has the right resolution
 *
 * ```php
 * use Phalcon\Filter\Validation;
 * use Phalcon\Filter\Validation\Validator\File\Resolution\Min;
 *
 * $validator = new Validation();
 *
 * $validator->add(
 *     "file",
 *     new Min(
 *         [
 *             "resolution" => "800x600",
 *             "message"    => "Min resolution of :field is :resolution",
 *             "included"   => true,
 *         ]
 *     )
 * );
 *
 * $validator->add(
 *     [
 *         "file",
 *         "anotherFile",
 *     ],
 *     new Min(
 *         [
 *             "resolution" => [
 *                 "file"        => "800x600",
 *                 "anotherFile" => "1024x768",
 *             ],
 *             "included" => [
 *                 "file"        => false,
 *                 "anotherFile" => true,
 *             ],
 *             "message" => [
 *                 "file"        => "Min resolution of file is 800x600",
 *                 "anotherFile" => "Min resolution of file is 1024x768",
 *             ],
 *         ]
 *     )
 * );
 * ```
 */
class Min extends Equal
{
    /**
     * @var string|null
     */
    protected ?string $template = "File :field can not have the minimum resolution of :resolution";

    /**
     * Executes the conditional
     *
     * @param int  $sourceWidth
     * @param int  $targetWidth
     * @param int  $sourceHeight
     * @param int  $targetHeight
     * @param bool $included
     *
     * @return bool
     */
    protected function getConditional(
        int $sourceWidth,
        int $targetWidth,
        int $sourceHeight,
        int $targetHeight,
        bool $included = false
    ): bool {
        if (true === $included) {
            return $sourceWidth <= $targetWidth || $sourceHeight <= $targetHeight;
        }

        return $sourceWidth < $targetWidth || $sourceHeight < $targetHeight;
    }
}
