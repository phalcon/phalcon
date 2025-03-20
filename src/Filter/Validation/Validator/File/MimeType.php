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
use Phalcon\Filter\Validation\Exception;
use Phalcon\Traits\Php\InfoTrait;

use function finfo_close;
use function finfo_file;
use function finfo_open;
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
    use InfoTrait;

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
     * @throws Exception
     */
    public function validate(Validation $validation, string $field): bool
    {
        // Check file upload
        if (true !== $this->checkUpload($validation, $field)) {
            return false;
        }

        $value = $validation->getValue($field);
        $types = $this->checkArray($this->getOption("types"), $field);

        if (!is_array($types)) {
            throw new Exception(
                "Option 'types' must be an array"
            );
        }
        if (true === $this->phpFunctionExists("finfo_open")) {
            $mimeType = finfo_open(FILEINFO_MIME_TYPE);
            $mime     = finfo_file($mimeType, $value["tmp_name"]);

            finfo_close($mimeType);
        } else {
            $mime = $value['type'];
        }

        if (true !== in_array($mime, $types)) {
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
