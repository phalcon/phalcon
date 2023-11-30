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

use Phalcon\Filter\Validation\AbstractValidatorComposite;
use Phalcon\Filter\Validation\Validator\File\MimeType;
use Phalcon\Filter\Validation\Validator\File\Resolution\Equal as EqualResolution;
use Phalcon\Filter\Validation\Validator\File\Resolution\Max as MaxResolution;
use Phalcon\Filter\Validation\Validator\File\Resolution\Min as MinResolution;
use Phalcon\Filter\Validation\Validator\File\Size\Equal as EqualFileSize;
use Phalcon\Filter\Validation\Validator\File\Size\Max as MaxFileSize;
use Phalcon\Filter\Validation\Validator\File\Size\Min as MinFileSize;
use Phalcon\Filter\Validation\ValidatorInterface;

/**
 * Checks if a value has a correct file
 *
 * ```php
 * use Phalcon\Filter\Validation;
 * use Phalcon\Filter\Validation\Validator\File as FileValidator;
 *
 * $validator = new Validation();
 *
 * $validator->add(
 *     "file",
 *     new FileValidator(
 *         [
 *             "maxSize"              => "2M",
 *             "messageSize"          => ":field exceeds the max file size (:size)",
 *             "allowedTypes"         => [
 *                 "image/jpeg",
 *                 "image/png",
 *             ],
 *             "messageType"          => "Allowed file types are :types",
 *             "maxResolution"        => "800x600",
 *             "messageMaxResolution" => "Max resolution of :field is :resolution",
 *             "messageFileEmpty"     => "File is empty",
 *             "messageIniSize"       => "Ini size is not valid",
 *             "messageValid"         => "File is not valid",
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
 *             "allowedTypes" => [
 *                 "file"        => [
 *                     "image/jpeg",
 *                     "image/png",
 *                 ],
 *                 "anotherFile" => [
 *                     "image/gif",
 *                     "image/bmp",
 *                 ],
 *             ],
 *             "messageType" => [
 *                 "file"        => "Allowed file types are image/jpeg and image/png",
 *                 "anotherFile" => "Allowed file types are image/gif and image/bmp",
 *             ],
 *             "maxResolution" => [
 *                 "file"        => "800x600",
 *                 "anotherFile" => "1024x768",
 *             ],
 *             "messageMaxResolution" => [
 *                 "file"        => "Max resolution of file is 800x600",
 *                 "anotherFile" => "Max resolution of file is 1024x768",
 *             ],
 *         ]
 *     )
 * );
 * ```
 */
class File extends AbstractValidatorComposite
{
    /**
     * Constructor
     *
     * @param array $options = [
     *                       'messageMinSize'         => '',
     *                       'includedMinSize'        => false,
     *                       'minSize'                => 100,
     *                       'maxSize'                => 1000,
     *                       'messageSize'            => '',
     *                       'includedSize'           => false,
     *                       'equalSize'              => '',
     *                       'messageEqualSize'       => '',
     *                       'allowedTypes'           => [],
     *                       'messageType'            => '',
     *                       'maxResolution'          => '1000x1000',
     *                       'messageMaxResolution'   => '',
     *                       'includedMaxResolution'  => false,
     *                       'minResolution =         > '500x500',
     *                       'includedMinResolution'  => false,
     *                       'messageMinResolution'   => '',
     *                       'equalResolution'        => '1000x1000',
     *                       'messageEqualResolution' => '',
     *                       'allowEmpty'             => false,
     *                       'messageFileEmpty'       => '',
     *                       'messageIniSize'         => '',
     *                       'messageValid'           => '',
     *                       ]
     */
    public function __construct(array $options = [])
    {
        $fileEmpty = (string)($options["messageFileEmpty"] ?? null);
        $iniSize   = (string)($options["messageIniSize"] ?? null);
        $valid     = (string)($options["messageValid"] ?? null);

        $this
            ->processFileAllowedType($options, $fileEmpty, $iniSize, $valid)
            ->processFileResolutionEqual($options, $fileEmpty, $iniSize, $valid)
            ->processFileResolutionMax($options, $fileEmpty, $iniSize, $valid)
            ->processFileResolutionMin($options, $fileEmpty, $iniSize, $valid)
            ->processFileSizeEquals($options, $fileEmpty, $iniSize, $valid)
            ->processFileSizeMax($options, $fileEmpty, $iniSize, $valid)
            ->processFileSizeMin($options, $fileEmpty, $iniSize, $valid)
        ;

        unset(
            $options["messageMinSize"],
            $options["includedMinSize"],
            $options["maxSize"],
            $options["messageSize"],
            $options["includedSize"],
            $options["equalSize"],
            $options["messageEqualSize"],
            $options["allowedTypes"],
            $options["messageType"],
            $options["maxResolution"],
            $options["includedMaxResolution"],
            $options["messageMaxResolution"],
            $options["minResolution"],
            $options["includedMinResolution"],
            $options["messageMinResolution"],
            $options["equalResolution"],
            $options["messageEqualResolution"]
        );

        parent::__construct($options);
    }

    /**
     * @param array  @options
     * @param string $fileEmpty
     * @param string $iniSize
     * @param string $valid
     *
     * @return File
     */
    private function processFileAllowedType(
        array $options,
        string $fileEmpty,
        string $iniSize,
        string $valid
    ): File {
        if (true === isset($options["allowedTypes"])) {
            $validator = new MimeType(
                [
                    "size"    => $options["allowedTypes"],
                    "message" => $options["messageType"] ?? null,
                ]
            );

            $this->processSettings(
                $validator,
                $fileEmpty,
                $iniSize,
                $valid
            );
        }

        return $this;
    }

    /**
     * @param array  @options
     * @param string $fileEmpty
     * @param string $iniSize
     * @param string $valid
     *
     * @return File
     */
    private function processFileResolutionEqual(
        array $options,
        string $fileEmpty,
        string $iniSize,
        string $valid
    ): File {
        if (true === isset($options["minSize"])) {
            $validator = new MinResolution(
                [
                    "size"    => $options["equalResolution"],
                    "message" => $options["messageEqualResolution"] ?? null,
                ]
            );

            $this->processSettings(
                $validator,
                $fileEmpty,
                $iniSize,
                $valid
            );
        }

        return $this;
    }

    /**
     * @param array  @options
     * @param string $fileEmpty
     * @param string $iniSize
     * @param string $valid
     *
     * @return File
     */
    private function processFileResolutionMax(
        array $options,
        string $fileEmpty,
        string $iniSize,
        string $valid
    ): File {
        if (true === isset($options["maxResolution"])) {
            $validator = new MaxResolution(
                [
                    "size"     => $options["maxResolution"],
                    "message"  => $options["messageMaxResolution"] ?? null,
                    "included" => $options["includedMaxResolution"] ?? false,
                ]
            );

            $this->processSettings(
                $validator,
                $fileEmpty,
                $iniSize,
                $valid
            );
        }

        return $this;
    }

    /**
     * @param array  @options
     * @param string $fileEmpty
     * @param string $iniSize
     * @param string $valid
     *
     * @return File
     */
    private function processFileResolutionMin(
        array $options,
        string $fileEmpty,
        string $iniSize,
        string $valid
    ): File {
        if (true === isset($options["minResolution"])) {
            $validator = new EqualResolution(
                [
                    "size"     => $options["minResolution"],
                    "message"  => $options["messageMinResolution"] ?? null,
                    "included" => $options["includedMinResolution"] ?? false,
                ]
            );

            $this->processSettings(
                $validator,
                $fileEmpty,
                $iniSize,
                $valid
            );
        }

        return $this;
    }

    /**
     * @param array  @options
     * @param string $fileEmpty
     * @param string $iniSize
     * @param string $valid
     *
     * @return File
     */
    private function processFileSizeEquals(
        array $options,
        string $fileEmpty,
        string $iniSize,
        string $valid
    ): File {
        if (true === isset($options["equalSize"])) {
            $validator = new EqualFileSize(
                [
                    "size"    => $options["equalSize"],
                    "message" => $options["messageEqualSize"] ?? null,
                ]
            );

            $this->processSettings(
                $validator,
                $fileEmpty,
                $iniSize,
                $valid
            );
        }

        return $this;
    }

    /**
     * @param array  @options
     * @param string $fileEmpty
     * @param string $iniSize
     * @param string $valid
     *
     * @return File
     */
    private function processFileSizeMax(
        array $options,
        string $fileEmpty,
        string $iniSize,
        string $valid
    ): File {
        if (true === isset($options["maxSize"])) {
            $validator = new MaxFileSize(
                [
                    "size"     => $options["maxSize"],
                    "message"  => $options["messageSize"] ?? null,
                    "included" => $options["includedSize"] ?? false,
                ]
            );

            $this->processSettings(
                $validator,
                $fileEmpty,
                $iniSize,
                $valid
            );
        }

        return $this;
    }

    /**
     * @param array  @options
     * @param string $fileEmpty
     * @param string $iniSize
     * @param string $valid
     *
     * @return File
     */
    private function processFileSizeMin(
        array $options,
        string $fileEmpty,
        string $iniSize,
        string $valid
    ): File {
        if (true === isset($options["minSize"])) {
            $validator = new MinFileSize(
                [
                    "size"     => $options["minSize"],
                    "message"  => $options["messageMinSize"] ?? null,
                    "included" => $options["includedMinSize"] ?? false,
                ]
            );

            $this->processSettings(
                $validator,
                $fileEmpty,
                $iniSize,
                $valid
            );
        }

        return $this;
    }

    /**
     * @param ValidatorInterface $validator
     * @param string             $messageFileEmpty
     * @param string             $messageIniSize
     * @param string             $messageValid
     *
     * @return void
     */
    private function processSettings(
        ValidatorInterface $validator,
        string $messageFileEmpty,
        string $messageIniSize,
        string $messageValid
    ): void {
        if (true !== empty($messageFileEmpty)) {
            $validator->setMessageFileEmpty($messageFileEmpty);
        }

        if (true !== empty($messageIniSize)) {
            $validator->setMessageIniSize($messageIniSize);
        }

        if (true !== empty($messageValid)) {
            $validator->setMessageValid($messageValid);
        }

        $this->validators[] = $validator;
    }
}
