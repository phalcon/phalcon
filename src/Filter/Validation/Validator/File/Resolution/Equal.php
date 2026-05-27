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
 */
class Equal extends AbstractFile
{
    /**
     * @var string|null
     */
    protected string | null $template = "The resolution of the field :field has to be equal :resolution";

    /**
     * Constructor
     *
     * @param array $options
     */
    public function __construct(array $options = [])
    {
        parent::__construct($options);
    }

    /**
     * Executes the validation
     *
     * @param Validation $validation
     * @param string     $field
     *
     * @return bool
     */
    public function validate(Validation $validation, string $field): bool
    {
        // Check file upload
        if (false === $this->checkUpload($validation, $field)) {
            return false;
        }

        $value  = $validation->getValue($field);
        $tmp    = getimagesize($value["tmp_name"]);
        $width  = $tmp[0];
        $height = $tmp[1];

        $resolution = $this->getOption("resolution");

        if (is_array($resolution)) {
            $resolution = $resolution[$field];
        }

        $resolutionArray = explode("x", $resolution);
        $equalWidth      = $resolutionArray[0];
        $equalHeight     = $resolutionArray[1];

        if (is_array($resolution)) {
            $resolution = $resolution[$field];
        }

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
