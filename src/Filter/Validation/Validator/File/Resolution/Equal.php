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
 *
 * @phpstan-import-type filter_validator_options from FilterTypes
 */
class Equal extends AbstractFile
{
    protected $template = "The resolution of the field :field has to be equal :resolution";

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

        [$resolution, $equalWidth, $equalHeight] = $this->getDimensionsOption($field, "resolution");

        if ($width != $equalWidth || $height != $equalHeight) {
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
