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

use Phalcon\Contracts\Filter\FilterTypes;
use Phalcon\Filter\Validation\AbstractValidatorComposite;
use Phalcon\Filter\Validation\Validator\File\MimeType;
use Phalcon\Filter\Validation\Validator\File\Resolution\AspectRatio;
use Phalcon\Filter\Validation\Validator\File\Resolution\Equal as EqualResolution;
use Phalcon\Filter\Validation\Validator\File\Resolution\Max as MaxResolution;
use Phalcon\Filter\Validation\Validator\File\Resolution\Min as MinResolution;
use Phalcon\Filter\Validation\Validator\File\Size\Equal as EqualFileSize;
use Phalcon\Filter\Validation\Validator\File\Size\Max as MaxFileSize;
use Phalcon\Filter\Validation\Validator\File\Size\Min as MinFileSize;

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
 *
 * @phpstan-import-type filter_validator_options from FilterTypes
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
     *                       'aspectRatio'            => '16x9',
     *                       'messageAspectRatio'     => '',
     *                       'allowEmpty'             => false,
     *                       'messageFileEmpty'       => '',
     *                       'messageIniSize'         => '',
     *                       'messageValid'           => '',
     *                       ]
     * @phpstan-param filter_validator_options $options
     */
    public function __construct(array $options = [])
    {
        $allowWildcards   = false;
        $messageFileEmpty = null;
        $messageIniSize   = null;
        $messageValid     = null;

        if (isset($options["messageFileEmpty"])) {
            /**
             * The option holds the message that the sub validator shows.
             *
             * @phpstan-var string $messageFileEmpty
             */
            $messageFileEmpty = $options["messageFileEmpty"];
            unset($options["messageFileEmpty"]);
        }

        if (isset($options["messageIniSize"])) {
            /**
             * The option holds the message that the sub validator shows.
             *
             * @phpstan-var string $messageIniSize
             */
            $messageIniSize = $options["messageIniSize"];
            unset($options["messageIniSize"]);
        }

        if (isset($options["messageValid"])) {
            /**
             * The option holds the message that the sub validator shows.
             *
             * @phpstan-var string $messageValid
             */
            $messageValid = $options["messageValid"];
            unset($options["messageValid"]);
        }

        if (isset($options["allowWildcards"])) {
            $allowWildcards = (bool)$options["allowWildcards"];
            unset($options["allowWildcards"]);
        }

        // create individual validators
        foreach ($options as $key => $value) {
            $key = (string)$key;

            // min file size
            if (strcasecmp($key, "minSize") === 0) {
                $validator = new MinFileSize(
                    [
                        "size"     => $value,
                        "message"  => $options["messageMinSize"] ?? null,
                        "included" => $options["includedMinSize"] ?? null,
                    ]
                );

                unset($options["minSize"]);
                unset($options["messageMinSize"]);
                unset($options["includedMinSize"]);
            } elseif (strcasecmp($key, "maxSize") === 0) {
                // max file size
                $validator = new MaxFileSize(
                    [
                        "size"     => $value,
                        "message"  => $options["messageSize"] ?? null,
                        "included" => $options["includedSize"] ?? null,
                    ]
                );

                unset($options["maxSize"]);
                unset($options["messageSize"]);
                unset($options["includedSize"]);
            } elseif (strcasecmp($key, "equalSize") === 0) {
                // equal file size
                $validator = new EqualFileSize(
                    [
                        "size"    => $value,
                        "message" => $options["messageEqualSize"] ?? null,
                    ]
                );

                unset($options["equalSize"]);
                unset($options["messageEqualSize"]);
            } elseif (strcasecmp($key, "allowedTypes") === 0) {
                // mime types
                $validator = new MimeType(
                    [
                        "types"          => $value,
                        "message"        => $options["messageType"] ?? null,
                        "allowWildcards" => $allowWildcards,
                    ]
                );

                unset($options["allowedTypes"]);
                unset($options["messageType"]);
            } elseif (strcasecmp($key, "maxResolution") === 0) {
                // max resolution
                $validator = new MaxResolution(
                    [
                        "resolution" => $value,
                        "included"   => $options["includedMaxResolution"] ?? null,
                        "message"    => $options["messageMaxResolution"] ?? null,
                    ]
                );

                unset($options["maxResolution"]);
                unset($options["includedMaxResolution"]);
                unset($options["messageMaxResolution"]);
            } elseif (strcasecmp($key, "minResolution") === 0) {
                // min resolution
                $validator = new MinResolution(
                    [
                        "resolution" => $value,
                        "included"   => $options["includedMinResolution"] ?? null,
                        "message"    => $options["messageMinResolution"] ?? null,
                    ]
                );

                unset($options["minResolution"]);
                unset($options["includedMinResolution"]);
                unset($options["messageMinResolution"]);
            } elseif (strcasecmp($key, "equalResolution") === 0) {
                // equal resolution
                $validator = new EqualResolution(
                    [
                        "resolution" => $value,
                        "message"    => $options["messageEqualResolution"] ?? null,
                    ]
                );

                unset($options["equalResolution"]);
                unset($options["messageEqualResolution"]);
            } elseif (strcasecmp($key, "aspectRatio") === 0) {
                // aspect ratio
                $validator = new AspectRatio(
                    [
                        "ratio"   => $value,
                        "message" => $options["messageAspectRatio"] ?? null,
                    ]
                );

                unset($options["aspectRatio"]);
                unset($options["messageAspectRatio"]);
            } else {
                continue;
            }

            if (null !== $messageFileEmpty) {
                $validator->setMessageFileEmpty((string)$messageFileEmpty);
            }

            if (null !== $messageIniSize) {
                $validator->setMessageIniSize((string)$messageIniSize);
            }

            if (null !== $messageValid) {
                $validator->setMessageValid((string)$messageValid);
            }

            $this->validators[] = $validator;
        }

        parent::__construct($options);
    }
}
