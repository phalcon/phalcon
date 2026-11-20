<?php

/**
 * This file is part of the Phalcon Framework.
 *
 * (c) Phalcon Team <team@phalcon.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Phalcon\Filter\Validation\Validator\File\Resolution;

use Phalcon\Contracts\Filter\FilterTypes;
use Phalcon\Filter\Validation;
use Phalcon\Filter\Validation\Validator\File\AbstractFile;
use Phalcon\Messages\Message;

use function is_array;

/**
 * Checks if a file has the right resolution
 *
 * ```php
 * use Phalcon\Filter\Validation;
 * use Phalcon\Filter\Validation\Validator\File\Resolution\Max;
 *
 * $validator = new Validation();
 *
 * $validator->add(
 *     "file",
 *     new Max(
 *         [
 *             "resolution"      => "800x600",
 *             "message"  => "Max resolution of :field is :resolution",
 *             "included" => true,
 *         ]
 *     )
 * );
 *
 * $validator->add(
 *     [
 *         "file",
 *         "anotherFile",
 *     ],
 *     new Max(
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
 *                 "file"        => "Max resolution of file is 800x600",
 *                 "anotherFile" => "Max resolution of file is 1024x768",
 *             ],
 *         ]
 *     )
 * );
 * ```
 *
 * @phpstan-import-type filter_validator_options from FilterTypes
 */
class Max extends AbstractFile
{
    protected $template = "File :field exceeds the maximum resolution of :resolution";

    /**
     * Constructor
     *
     * @phpstan-param filter_validator_options $options
     */
    public function __construct(array $options = [])
    {
        parent::__construct($options);
    }

    /**
     * Executes the validation
     */
    public function validate(Validation $validation, mixed $field): bool
    {
        /**
         * Validation iterates its validators by field name, so the field is
         * a string here. The parameter is mixed to match the untyped Zephir
         * signature.
         *
         * @var string $field
         */
        $size = $this->getUploadedImageSize($validation, $field);

        if (false === $size) {
            return false;
        }

        $width  = $size[0];
        $height = $size[1];

        [$resolution, $maxWidth, $maxHeight] = $this->getDimensionsOption($field, "resolution");

        $included = $this->getOption("included");

        if (is_array($included)) {
            $included = (bool)$included[$field];
        } else {
            $included = (bool)$included;
        }

        if ($included) {
            $result = $width >= $maxWidth || $height >= $maxHeight;
        } else {
            $result = $width > $maxWidth || $height > $maxHeight;
        }

        if ($result) {
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
}
