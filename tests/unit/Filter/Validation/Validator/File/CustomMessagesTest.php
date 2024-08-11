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

final class CustomMessagesTest extends AbstractUnitTestCase
{
    /**
     * Tests Phalcon\Filter\Validation\Validator\File :: customMessages[]
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2018-11-13
     */
    public function testFilterValidationValidatorFileCustomMessages(): void
    {
        $options    = [
            'maxSize'          => '0.5M',
            'messageFileEmpty' => 'File is empty',
            'messageIniSize'   => 'Ini size is not valid',
            'messageValid'     => 'File is not valid',
        ];
        $file       = new File($options);
        $validators = $file->getValidators();

        /** @var File\AbstractFile $validator */
        foreach ($validators as $validator) {
            $expected = $options['messageFileEmpty'];
            $actual   = $validator->getMessageFileEmpty();
            $this->assertSame($expected, $actual);

            $expected = $options['messageIniSize'];
            $actual   = $validator->getMessageIniSize();
            $this->assertSame($expected, $actual);

            $expected = $options['messageValid'];
            $actual   = $validator->getMessageValid();
            $this->assertSame($expected, $actual);
        }
    }
}
