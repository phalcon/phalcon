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

use Phalcon\Contracts\Filter\FilterTypes;
use Phalcon\Filter\Validation;
use Phalcon\Filter\Validation\Validator\File\AbstractFile;
use Phalcon\Messages\Message;

use function explode;
use function getimagesize;
use function is_array;

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
 * @phpstan-import-type filter_uploaded_file from FilterTypes
 */
class Equal extends AbstractFile
{
    protected string | null $template = "The resolution of the field :field has to be equal :resolution";

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
        // Check file upload
        if (false === $this->checkUpload($validation, $field)) {
            return false;
        }

        /**
         * `checkUpload()` rejects anything that is not a usable uploaded
         * file, so the value is a $_FILES entry from here on.
         *
         * @var filter_uploaded_file $value
         */
        $value = $validation->getValue($field);
        $tmp   = getimagesize($value["tmp_name"]);

        // The file cannot be read as an image
        if (false === $tmp) {
            $this->appendMessageValid($validation, $field);

            return false;
        }

        $width  = $tmp[0];
        $height = $tmp[1];

        $resolution = $this->getOption("resolution");

        if (is_array($resolution)) {
            $resolution = $resolution[$field];
        }

        /** @phpstan-var string $resolution */
        $resolutionArray = explode("x", $resolution);
        $equalWidth      = $resolutionArray[0];
        $equalHeight     = $resolutionArray[1];

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
