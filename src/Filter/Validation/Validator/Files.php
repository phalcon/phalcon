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

namespace Phalcon\Filter\Validation\Validator;

use Phalcon\Filter\Validation;
use Phalcon\Filter\Validation\AbstractValidator;

use function is_array;

use const UPLOAD_ERR_NO_FILE;

/**
 * Validates an array of uploaded files by delegating each file to the `File`
 * validator. Accepts the same options as `Phalcon\Filter\Validation\Validator\File`
 * and forwards them to each delegated file. A standard multiple-file upload
 * (`<input name="files[]" type="file" multiple>`) arrives as a transposed
 * `$_FILES` node; this validator normalizes it into individual files and fails
 * on the first file that violates a rule.
 *
 * ```php
 * use Phalcon\Filter\Validation;
 * use Phalcon\Filter\Validation\Validator\Files as FilesValidator;
 *
 * $validation = new Validation();
 *
 * $validation->add(
 *     "photos",
 *     new FilesValidator(
 *         [
 *             "maxSize"      => "2M",
 *             "messageSize"  => ":field exceeds the max file size (:size)",
 *             "allowedTypes" => ["image/jpeg", "image/png"],
 *             "messageType"  => "Allowed file types are :types",
 *         ]
 *     )
 * );
 * ```
 */
class Files extends AbstractValidator
{
    /**
     * Whole-field empty check: true when the field carries no uploaded files.
     *
     * @param Validation $validation
     * @param string     $field
     *
     * @return bool
     */
    public function isAllowEmpty(Validation $validation, string $field): bool
    {
        $value = $validation->getValue($field);

        if (empty($value)) {
            return true;
        }

        $files = $this->normalizeFiles($value);

        foreach ($files as $single) {
            if (
                !is_array($single) ||
                !isset($single["error"]) ||
                $single["error"] !== UPLOAD_ERR_NO_FILE
            ) {
                return false;
            }
        }

        return true;
    }

    /**
     * Executes the validation, delegating each file to a `File` validator.
     *
     * @param Validation $validation
     * @param string     $field
     *
     * @return bool
     * @throws Validation\Exception
     */
    public function validate(Validation $validation, string $field): bool
    {
        $value     = $validation->getValue($field);
        $files     = $this->normalizeFiles($value);
        $validator = new File($this->options);

        foreach ($files as $single) {
            $inner = new Validation();
            $inner->add($field, $validator);

            $messages = $inner->validate([$field => $single]);

            if ($messages->count() > 0) {
                foreach ($messages as $message) {
                    $validation->appendMessage($message);
                }

                return false;
            }
        }

        return true;
    }

    /**
     * Normalizes a single file or a transposed multi-file `$_FILES` node into a
     * list of single-file structures.
     *
     * @param mixed $value
     *
     * @return array
     */
    protected function normalizeFiles(mixed $value): array
    {
        // Not an array (e.g. null/missing) -> delegate once so File reports it
        if (!is_array($value)) {
            return [$value];
        }

        // Single-file structure: "name" is a scalar, not an array
        if (!isset($value["name"]) || !is_array($value["name"])) {
            return [$value];
        }

        $files = [];

        foreach ($value["name"] as $index => $name) {
            $files[] = [
                "name"     => $name,
                "type"     => $value["type"][$index] ?? null,
                "tmp_name" => $value["tmp_name"][$index] ?? null,
                "size"     => $value["size"][$index] ?? null,
                "error"    => $value["error"][$index] ?? null,
            ];
        }

        return $files;
    }
}
