<?php

/**
 * This file is part of the Phalcon Framework.
 *
 * (c) Phalcon Team <team@phalcon.io>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Phalcon\Tests\Unit\Filter;

use Phalcon\Filter\FilterFactory;
use Phalcon\Filter\Validation;
use Phalcon\Filter\Validation\Exceptions\FieldNotPrintable;
use Phalcon\Filter\Validation\Validator\Confirmation;
use Phalcon\Filter\Validation\Validator\Date;
use Phalcon\Filter\Validation\Validator\File\Size\Equal;
use Phalcon\Filter\Validation\Validator\PresenceOf;
use Phalcon\Filter\Validation\Validator\StringLength;
use Phalcon\Talon\PHPUnit\AbstractUnitTestCase;

use function restore_error_handler;
use function set_error_handler;

/**
 * cphalcon uses the PHP weak type rules for an int value that goes to a string
 * target, thus the int becomes a string.
 *
 * @issue https://github.com/phalcon/phalcon/issues/886
 */
final class WeakModeIntValuesTest extends AbstractUnitTestCase
{
    /**
     * @author Phalcon Team <team@phalcon.io>
     * @since  2026-11-19
     */
    public function testFilterSanitizeIntValues(): void
    {
        $filter = (new FilterFactory())->newInstance();

        $this->assertSame('123', $filter->sanitize(123, 'alnum'));
        $this->assertSame('', $filter->sanitize(123, 'alpha'));
        $this->assertSame('1x3', $filter->sanitize(123, ['regex' => ['/2/', 'x']]));
        $this->assertSame('a0', $filter->sanitize('a1', ['regex' => ['/\d/', 0]]));
        $this->assertSame('a-b', $filter->sanitize('a0b', ['replace' => [0, '-']]));
        $this->assertSame('a0b', $filter->sanitize('a-b', ['replace' => ['-', 0]]));
        $this->assertSame('ab', $filter->sanitize('a0b', ['remove' => [0]]));
    }

    /**
     * @author Phalcon Team <team@phalcon.io>
     * @since  2026-11-19
     */
    public function testFilterSanitizeRemoveReplaceIntInput(): void
    {
        $filter   = (new FilterFactory())->newInstance();
        $warnings = [];
        set_error_handler(
            function (int $number, string $message) use (&$warnings): bool {
                $warnings[] = $message;

                return true;
            }
        );

        try {
            $remove  = $filter->sanitize(1203, ['remove' => ['0']]);
            $replace = $filter->sanitize(1203, ['replace' => ['0', 'x']]);
        } finally {
            restore_error_handler();
        }

        $this->assertNull($remove);
        $this->assertNull($replace);
        $this->assertCount(2, $warnings);
        $this->assertStringContainsString('Invalid arguments supplied for str_replace()', $warnings[0]);
    }

    /**
     * @author Phalcon Team <team@phalcon.io>
     * @since  2026-11-19
     */
    public function testFilterValidationFileSizeInt(): void
    {
        $validator = new class (['size' => 1024]) extends Equal {
            public function checkUpload(Validation $validation, string $field): bool
            {
                return true;
            }
        };

        $validation = new Validation();
        $validation->add('f', $validator);

        $file = [
            'name'     => 'a.txt',
            'type'     => 'text/plain',
            'tmp_name' => '/srv/x',
            'error'    => 0,
            'size'     => 1024,
        ];
        $this->assertCount(0, $validation->validate(['f' => $file]));
    }

    /**
     * @author Phalcon Team <team@phalcon.io>
     * @since  2026-11-19
     */
    public function testFilterValidationIntOptions(): void
    {
        $validation = new Validation();
        $validation->setLabels(['name' => 123]);
        $validation->add('name', new PresenceOf());

        $this->assertSame('Field 123 is required', $validation->validate(['name' => ''])[0]->getMessage());

        Validation::setDefaultMessages([PresenceOf::class => 123]);

        try {
            $validation = new Validation();
            $validation->add('name', new PresenceOf());

            $this->assertSame('123', $validation->validate(['name' => ''])[0]->getMessage());
        } finally {
            Validation::setDefaultMessages([PresenceOf::class => '']);
        }

        $this->assertNull((new Validation())->getValueByEntity(123, 'name'));

        $validation = new Validation();
        $validation->add('d', new Date(['format' => 1]));
        $this->assertCount(0, $validation->validate(['d' => '1']));

        $validation = new Validation();
        $validation->add('a', new Confirmation(['with' => 1]));
        $this->assertCount(0, $validation->validate(['a' => 'x', 1 => 'x']));

        $validation = new Validation();
        $validation->add('name', new StringLength([0 => 'x', 'max' => 5]));
        $this->assertCount(0, $validation->validate(['name' => 'abc']));
    }

    /**
     * @author Phalcon Team <team@phalcon.io>
     * @since  2026-11-19
     */
    public function testFilterValidationNumericFieldName(): void
    {
        $validation = new Validation();
        $validation->add('1', new PresenceOf());

        $this->assertCount(0, $validation->validate([1 => 'x']));

        $this->expectException(FieldNotPrintable::class);
        $this->expectExceptionMessage('The field can not be printed');

        $validation->validate([1 => '']);
    }
}
