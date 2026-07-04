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

namespace Phalcon\Filter\Validation\Validator\File;

use Phalcon\Filter\Validation;
use Phalcon\Filter\Validation\Exceptions\InvalidAllowedTypes;

use function finfo_file;
use function finfo_open;
use function function_exists;
use function implode;
use function in_array;
use function is_array;

use const FILEINFO_MIME_TYPE;

/**
 * Checks if a value has a correct file mime type
 *
 * ```php
 * use Phalcon\Filter\Validation;
 * use Phalcon\Filter\Validation\Validator\File\MimeType;
 *
 * $validator = new Validation();
 *
 * $validator->add(
 *     "file",
 *     new MimeType(
 *         [
 *             "types" => [
 *                 "image/jpeg",
 *                 "image/png",
 *             ],
 *             "message" => "Allowed file types are :types"
 *         ]
 *     )
 * );
 *
 * $validator->add(
 *     [
 *         "file",
 *         "anotherFile",
 *     ],
 *     new MimeType(
 *         [
 *             "types" => [
 *                 "file"        => [
 *                     "image/jpeg",
 *                     "image/png",
 *                 ],
 *                 "anotherFile" => [
 *                     "image/gif",
 *                     "image/bmp",
 *                 ],
 *             ],
 *             "message" => [
 *                 "file"        => "Allowed file types are image/jpeg and image/png",
 *                 "anotherFile" => "Allowed file types are image/gif and image/bmp",
 *             ]
 *         ]
 *     )
 * );
 * ```
 */
class MimeType extends AbstractFile
{
    /**
     * @var string|null
     */
    protected string | null $template = "File :field must be of type: :types";

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

        $value = $validation->getValue($field);
        $types = $this->getOption("types");

        if (isset($types[$field])) {
            $types = $types[$field];
        }

        if (!is_array($types)) {
            throw new InvalidAllowedTypes();
        }

        $mime = null;
        if (function_exists("finfo_open")) {
            $tmp = finfo_open(FILEINFO_MIME_TYPE);
            if ($tmp) {
                $mime = finfo_file($tmp, $value["tmp_name"]);
            }
        }

        if (!$mime) {
            $mime = $value["type"];
        }

        if (!in_array($mime, $types)) {
            $replacePairs = [
                ":types" => implode(", ", $types),
            ];

            $validation->appendMessage(
                $this->messageFactory($validation, $field, $replacePairs)
            );

            return false;
        }

        return true;
    }
}
