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

use Phalcon\Contracts\Filter\FilterTypes;
use Phalcon\Filter\Validation;
use Phalcon\Filter\Validation\Exceptions\InvalidAllowedTypes;
use Phalcon\Traits\Php\InfoTrait;

use function finfo_file;
use function finfo_open;
use function implode;
use function in_array;
use function is_array;
use function preg_match;

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
 *
 * @phpstan-import-type filter_uploaded_file from FilterTypes
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
        /** @phpstan-var array<array-key, mixed>|null $types */
        $types = $this->getOption("types");

        if (isset($types[$field])) {
            $types = $types[$field];
        }

        if (!is_array($types)) {
            throw new InvalidAllowedTypes();
        }

        /** @phpstan-var array<array-key, string> $allowedTypes */
        $allowedTypes = $types;

        $mime = null;
        if ($this->phpFunctionExists("finfo_open")) {
            $tmp = finfo_open(FILEINFO_MIME_TYPE);
            if ($tmp) {
                $mime = finfo_file($tmp, $value["tmp_name"]);
            }
        }

        if (!$mime) {
            $mime = $value["type"];
        }

        $allowWildcards = (bool) $this->getOption("allowWildcards", false);

        $matched = false;
        if ($allowWildcards) {
            foreach ($allowedTypes as $type) {
                if (
                    $mime === $type ||
                    preg_match("#^" . (string) $type . "$#", (string) $mime)
                ) {
                    $matched = true;

                    break;
                }
            }
        } else {
            $matched = in_array($mime, $allowedTypes);
        }

        if (!$matched) {
            $replacePairs = [
                ":types" => implode(", ", $allowedTypes),
            ];

            $validation->appendMessage(
                $this->messageFactory($validation, $field, $replacePairs)
            );

            return false;
        }

        return true;
    }
}
