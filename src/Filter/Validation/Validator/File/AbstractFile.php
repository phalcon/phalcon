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
use Phalcon\Filter\Validation\AbstractValidator;
use Phalcon\Messages\Message;

use function array_keys;
use function floatval;
use function get_class;
use function implode;
use function is_array;
use function is_uploaded_file;
use function pow;
use function preg_match;

use const UPLOAD_ERR_INI_SIZE;
use const UPLOAD_ERR_NO_FILE;
use const UPLOAD_ERR_OK;

/**
 * Checks if a value has a correct file
 *
 * ```php
 * use Phalcon\Filter\Validation;
 * use Phalcon\Filter\Validation\Validator\File\Size;
 *
 * $validator = new Validation();
 *
 * $validator->add(
 *     "file",
 *     new Size(
 *         [
 *             "maxSize"              => "2M",
 *             "messageSize"          => ":field exceeds the max file size (:size)",
 *         ]
 *     )
 * );
 *
 * $validator->add(
 *     [
 *         "file",
 *         "anotherFile",
 *     ],
 *     new FileValidator(
 *         [
 *             "maxSize" => [
 *                 "file"        => "2M",
 *                 "anotherFile" => "4M",
 *             ],
 *             "messageSize" => [
 *                 "file"        => "file exceeds the max file size 2M",
 *                 "anotherFile" => "anotherFile exceeds the max file size 4M",
 *             ],
 *         ]
 *     )
 * );
 * ```
 */
abstract class AbstractFile extends AbstractValidator
{
    /**
     * Empty is empty
     *
     * @var string
     */
    protected string $messageFileEmpty = "Field :field must not be empty";

    /**
     * File exceeds the file size set in PHP configuration
     *
     * @var string
     */
    protected string $messageIniSize = "File :field exceeds the maximum file size";

    /**
     * File is not valid
     *
     * @var string
     */
    protected string $messageValid = "Field :field is not valid";

    /**
     * Check upload
     *
     * @param Validation $validation
     * @param string     $field
     *
     * @return bool
     * @throws Validation\Exception
     */
    public function checkUpload(Validation $validation, string $field): bool
    {
        return $this->checkUploadMaxSize($validation, $field) &&
            $this->checkUploadIsEmpty($validation, $field) &&
            $this->checkUploadIsValid($validation, $field);
    }

    /**
     * Check if upload is empty
     *
     * @param Validation $validation
     * @param string     $field
     *
     * @return bool
     * @throws Validation\Exception
     */
    public function checkUploadIsEmpty(
        Validation $validation,
        string $field
    ): bool {
        $value = $validation->getValue($field);

        if (
            is_array($value) &&
            (
                true !== isset($value["error"]) ||
                true !== isset($value["tmp_name"]) ||
                $value["error"] !== UPLOAD_ERR_OK ||
                true !== $this->checkIsUploadedFile($value["tmp_name"])
            )
        ) {
            $label        = $this->prepareLabel($validation, $field);
            $replacePairs = [
                ":field" => $label
            ];

            $validation->appendMessage(
                new Message(
                    strtr($this->getMessageFileEmpty(), $replacePairs),
                    $field,
                    get_class($this),
                    $this->prepareCode($field)
                )
            );

            return false;
        }

        return true;
    }

    /**
     * Check if upload is valid
     *
     * @param Validation $validation
     * @param string     $field
     *
     * @return bool
     * @throws Validation\Exception
     */
    public function checkUploadIsValid(
        Validation $validation,
        string $field
    ): bool {
        $value = $validation->getValue($field);

        if (
            is_array($value) &&
            (
                true !== isset($value["name"]) ||
                true !== isset($value["type"]) ||
                true !== isset($value["size"])
            )
        ) {
            $label        = $this->prepareLabel($validation, $field);
            $replacePairs = [
                ":field" => $label,
            ];

            $validation->appendMessage(
                new Message(
                    strtr($this->getMessageValid(), $replacePairs),
                    $field,
                    get_class($this),
                    $this->prepareCode($field)
                )
            );

            return false;
        }

        return true;
    }

    /**
     * Check if uploaded file is larger than PHP allowed size
     *
     * @param Validation $validation
     * @param string     $field
     *
     * @return bool
     * @throws Validation\Exception
     */
    public function checkUploadMaxSize(
        Validation $validation,
        string $field
    ): bool {
        $value  = $validation->getValue($field);
        $server = $_SERVER ?? [];
        $post   = $_POST ?? [];
        $files  = $_FILES ?? [];
        $method = $server["REQUEST_METHOD"] ?? "GET";
        $length = $server["CONTENT_LENGTH"] ?? 0;

        // Upload is larger than PHP allowed size (post_max_size or upload_max_filesize)
        if (
            "POST" === $method &&
            true === empty($post) &&
            true === empty($files) &&
            (int) $length > 0 ||
            is_array($value) &&
            true === isset($value["error"]) &&
            $value["error"] === UPLOAD_ERR_INI_SIZE
        ) {
            $label        = $this->prepareLabel($validation, $field);
            $replacePairs = [
                ":field" => $label,
            ];

            $validation->appendMessage(
                new Message(
                    strtr($this->getMessageIniSize(), $replacePairs),
                    $field,
                    get_class($this),
                    $this->prepareCode($field)
                )
            );

            return false;
        }

        return true;
    }

    /**
     * Convert a string like "2.5MB" in bytes
     *
     * @param string $size
     *
     * @return float
     */
    public function getFileSizeInBytes(string $size): float
    {
        $byteUnits = [
            "B"  => 0,
            "K"  => 10,
            "M"  => 20,
            "G"  => 30,
            "T"  => 40,
            "KB" => 10,
            "MB" => 20,
            "GB" => 30,
            "TB" => 40,
        ];
        $unit      = "B";
        $matches   = [];

        preg_match(
            "/^([0-9]+(?:\\.[0-9]+)?)("
            . implode("|", array_keys($byteUnits))
            . ")?$/Di",
            $size,
            $matches
        );

        if (true === isset($matches[2])) {
            $unit = $matches[2];
        }

        return floatval($matches[1]) * pow(2, $byteUnits[$unit]);
    }

    /**
     * Empty is empty
     *
     * @return string
     */
    public function getMessageFileEmpty(): string
    {
        return $this->messageFileEmpty;
    }

    /**
     * File exceeds the file size set in PHP configuration
     *
     * @return string
     */
    public function getMessageIniSize(): string
    {
        return $this->messageIniSize;
    }

    /**
     * File is not valid
     *
     * @return string
     */
    public function getMessageValid(): string
    {
        return $this->messageValid;
    }

    /**
     * Check on empty
     *
     * @param Validation $validation
     * @param string     $field
     *
     * @return bool
     * @throws Validation\Exception
     */
    public function isAllowEmpty(Validation $validation, string $field): bool
    {
        $value = $validation->getValue($field);

        return true === empty($value) ||
            is_array($value) &&
            true === isset($value["error"]) &&
            $value["error"] === UPLOAD_ERR_NO_FILE;
    }

    /**
     * Empty is empty
     *
     * @param string $message
     *
     * @return void
     */
    public function setMessageFileEmpty(string $message): void
    {
        $this->messageFileEmpty = $message;
    }

    /**
     * File exceeds the file size set in PHP configuration
     *
     * @param string $message
     *
     * @return void
     */
    public function setMessageIniSize(string $message): void
    {
        $this->messageIniSize = $message;
    }

    /**
     * File is not valid
     *
     * @param string $message
     *
     * @return void
     */
    public function setMessageValid(string $message): void
    {
        $this->messageValid = $message;
    }

    /**
     * Checks if a file has been uploaded; Internal check that can be
     * overridden in a subclass if you do not want to check uploaded files
     *
     * @param string $name
     *
     * @return bool
     */
    protected function checkIsUploadedFile(string $name): bool
    {
        return is_uploaded_file($name);
    }
}
