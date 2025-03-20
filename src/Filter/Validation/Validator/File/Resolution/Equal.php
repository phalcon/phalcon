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

use Phalcon\Filter\Validation;
use Phalcon\Filter\Validation\Validator\File\AbstractFile;

use function explode;
use function getimagesize;

/**
 * Checks if a file has the right resolution
 *
 * ```php
 * use Phalcon\Filter\Validation;
 * use Phalcon\Filter\Validation\Validator\File\Resolution\Equal;
 *
 * $validator = new Validation();
 *
 * $validator->add(
 *     "file",
 *     new Equal(
 *         [
 *             "resolution" => "800x600",
 *             "message"    => "The resolution of the field :field has to be equal :resolution",
 *         ]
 *     )
 * );
 *
 * $validator->add(
 *     [
 *         "file",
 *         "anotherFile",
 *     ],
 *     new Equal(
 *         [
 *             "resolution" => [
 *                 "file"        => "800x600",
 *                 "anotherFile" => "1024x768",
 *             ],
 *             "message" => [
 *                 "file"        => "Equal resolution of file has to be 800x600",
 *                 "anotherFile" => "Equal resolution of file has to be 1024x768",
 *             ],
 *         ]
 *     )
 * );
 * ```
 */
class Equal extends AbstractFile
{
    /**
     * @var string|null
     */
    protected string | null $template = "The resolution of the field :field has to be equal :resolution";

    /**
     * Executes the validation
     *
     * @param Validation $validation
     * @param string     $field
     *
     * @return bool
     * @throws Validation\Exception
     */
    public function validate(Validation $validation, string $field): bool
    {
        // Check file upload
        if (true !== $this->checkUpload($validation, $field)) {
            return false;
        }

        $value   = $validation->getValue($field);
        $tmpName = getimagesize($value["tmp_name"]);
        $width   = (int)$tmpName[0];
        $height  = (int)$tmpName[1];

        $resolution = $this->checkArray($this->getOption("resolution"), $field);

        $resolutionArray = explode("x", $resolution);
        $equalWidth      = (int)$resolutionArray[0];
        $equalHeight     = (int)$resolutionArray[1];

        $included = (bool)$this->checkArray(
            $this->getOption("included", false),
            $field
        );

        if (
            true === $this->getConditional(
                $width,
                $equalWidth,
                $height,
                $equalHeight,
                $included
            )
        ) {
            $replacePairs = [
                ":resolution" => $resolution,
            ];

            $validation->appendMessage(
                $this->messageFactory($validation, $field, $replacePairs)
            );

            return false;
        }

        return true;
    }

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
        return false === $included &&
            ($sourceWidth !== $targetWidth || $sourceHeight !== $targetHeight);
    }
}
