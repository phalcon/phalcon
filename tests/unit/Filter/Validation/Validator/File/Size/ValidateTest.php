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

namespace Phalcon\Tests\Unit\Filter\Validation\Validator\File\Size;

use Phalcon\Filter\Validation;
use Phalcon\Filter\Validation\Validator\File\Size\Equal;
use Phalcon\Filter\Validation\Validator\File\Size\Max;
use Phalcon\Filter\Validation\Validator\File\Size\Min;
use Phalcon\Tests\Fixtures\Filter\Validation\Validator\File\Size\EqualFixture;
use Phalcon\Tests\Fixtures\Filter\Validation\Validator\File\Size\MaxFixture;
use Phalcon\Tests\Fixtures\Filter\Validation\Validator\File\Size\MinFixture;
use Phalcon\Tests\AbstractUnitTestCase;
use PHPUnit\Framework\Attributes\BackupGlobals;
use PHPUnit\Framework\Attributes\DataProvider;

#[BackupGlobals(true)]
final class ValidateTest extends AbstractUnitTestCase
{
    /**
     * @return array[]
     */
    public static function getExamples(): array
    {
        return [
            [
                MinFixture::class,
                [
                    'size' => '1K',
                    ':field is smaller than file size (:size)',
                ],
            ],
            [
                MaxFixture::class,
                [
                    'size' => '20K',
                    ':field is larger than file size (:size)',
                ],
            ],
            [
                EqualFixture::class,
                [
                    'size' => '11768',
                    ':field is not equal to file size (:size)',
                ],
            ],
        ];
    }

    /**
     * @return array[]
     */
    public static function getExamplesErrors(): array
    {
        return [
            [
                MinFixture::class,
                'thumbnail is smaller than file size (20K)',
                [
                    'size' => '20K',
                    ':field is smaller than file size (:size)',
                ],
            ],
            [
                MinFixture::class,
                'thumbnail is smaller or equal than file size (11768)',
                [
                    'included' => true,
                    'size'     => '11768',
                    'message'  => ':field is smaller or equal than file size (:size)',
                ],
            ],
            [
                MaxFixture::class,
                'thumbnail is larger than file size (10K)',
                [
                    'size' => '10K',
                    ':field is larger than file size (:size)',
                ],
            ],
            [
                MaxFixture::class,
                'thumbnail is larger or equal than file size (11768)',
                [
                    'included' => true,
                    'size'     => '11768',
                    'message'  => ':field is larger or equal than file size (:size)',
                ],
            ],
            [
                EqualFixture::class,
                'thumbnail is not equal to file size (11700)',
                [
                    'size' => '11700',
                    ':field is not equal to file size (:size)',
                ],
            ],
        ];
    }

    /**
     * Tests Phalcon\Filter\Validation\Validator\File\Size :: validate
     *
     * @return void
     * @throws Validation\Exception
     * @author       Phalcon Team <team@phalcon.io>
     * @since        2023-09-28
     *
     */
    #[DataProvider("getExamples")]
    public function testFilterValidationValidatorFileSize(
        string $class,
        array $options
    ): void {
        $_SERVER = [
            'REQUEST_METHOD' => 'POST',
        ];

        $_FILES = [
            'thumbnail' => [
                'name'      => 'IMG-20180403-WA0000.jpg',
                'full_path' => 'IMG-20180403-WA0000.jpg',
                'type'      => 'image/jpeg',
                'tmp_name'  => '/tmp/phpsjLIQJ',
                'error'     => 0,
                'size'      => 11768,
            ],
        ];

        $validator = new $class($options);

        $validation = new Validation();
        $validation->add('thumbnail', $validator);

        $messages = $validation->validate($_FILES);

        $this->assertCount(0, $messages);
    }

    /**
     * Tests Phalcon\Filter\Validation\Validator\File\Size :: errors
     *
     *
     * @return void
     * @throws Validation\Exception
     *
     * @author       Phalcon Team <team@phalcon.io>
     * @since        2023-09-28
     */
    #[DataProvider("getExamplesErrors")]
    public function testFilterValidationValidatorFileSizeErrors(
        string $class,
        string $message,
        array $options
    ): void {
        $_SERVER = [
            'REQUEST_METHOD' => 'POST',
        ];
        $_FILES  = [
            'thumbnail' => [
                'name'      => 'IMG-20180403-WA0000.jpg',
                'full_path' => 'IMG-20180403-WA0000.jpg',
                'type'      => 'image/jpeg',
                'tmp_name'  => '/tmp/phpsjLIQJ',
                'error'     => 0,
                'size'      => 11768,
            ],
        ];

        $validator = new $class($options);

        $validation = new Validation();
        $validation->add('thumbnail', $validator);

        $messages = $validation->validate($_FILES);

        $this->assertCount(1, $messages);

        $expected = $message;
        $actual   = $messages[0]->getMessage();
        $this->assertSame($expected, $actual);
    }
}
