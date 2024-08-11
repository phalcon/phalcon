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

namespace Phalcon\Tests\Unit\Filter\Validation\Validator\File;

use Phalcon\Filter\Validation\Validator\File;
use Phalcon\Tests\AbstractUnitTestCase;
use PHPUnit\Framework\Attributes\Test;

final class ValidateTest extends AbstractUnitTestCase
{
    /**
     * Tests Phalcon\Filter\Validation\Validator\File :: validate()
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2018-11-13
     */
    public function testFilterValidationValidatorFileValidate(): void
    {
        $this->markTestSkipped('Need implementation');
    }
    /**
     * Tests Phalcon\Filter\Validation\Validator\File :: customMessages[]
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2018-11-13
     */
    #[Test]
    public function testFilterValidationValidatorFileValidateAllowedTypes(): void
    {
        $options    = [
            'allowedTypes'     => ['image/jpeg', 'image/png'],
            'maxSize'          => '0.5M',
            'minSize'          => '0.1M',
            'maxResolution'    => '800x600',
            'minResolution'    => '200x200',
            'messageFileEmpty' => 'File is empty',
            'messageIniSize'   => 'Ini size is not valid',
            'messageValid'     => 'File is not valid',
        ];
        $file       = new File($options);
        $validators = $file->getValidators();

        $this->assertCount(5, $validators);

        $expected  = File\MimeType::class;
        $actual    = $validators[0];
        $this->assertInstanceOf($expected, $actual);

        $expected  = File\Resolution\Max::class;
        $actual    = $validators[1];
        $this->assertInstanceOf($expected, $actual);

        $expected  = File\Resolution\Min::class;
        $actual = $validators[2];
        $this->assertInstanceOf($expected, $actual);

        $expected  = File\Size\Max::class;
        $actual    = $validators[3];
        $this->assertInstanceOf($expected, $actual);

        $expected  = File\Size\Min::class;
        $actual = $validators[4];
        $this->assertInstanceOf($expected, $actual);
    }
}
