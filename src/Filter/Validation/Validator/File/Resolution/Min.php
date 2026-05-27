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
class Min extends AbstractFile
{
    /**
     * @var string|null
     */
    protected string | null $template = "File :field can not have the minimum resolution of :resolution";

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
        $minWidth        = $resolutionArray[0];
        $minHeight       = $resolutionArray[1];

        $included = $this->getOption("included");

        if (is_array($included)) {
            $included = (bool)$included[$field];
        } else {
            $included = (bool)$included;
        }

        if ($included) {
            $result = $width <= $minWidth || $height <= $minHeight;
        } else {
            $result = $width < $minWidth || $height < $minHeight;
        }

        if (is_array($resolution)) {
            $resolution = $resolution[$field];
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
