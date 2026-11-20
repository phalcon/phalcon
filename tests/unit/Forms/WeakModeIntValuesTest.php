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

namespace Phalcon\Tests\Unit\Forms;

use Phalcon\Forms\Element\Text;
use Phalcon\Forms\Form;
use Phalcon\Talon\PHPUnit\AbstractUnitTestCase;
use stdClass;

use function restore_error_handler;
use function set_error_handler;

/**
 * cphalcon uses the PHP weak type rules for an int value that goes to a string
 * target, thus the int becomes a string.
 *
 * @issue https://github.com/phalcon/phalcon/issues/887
 */
final class WeakModeIntValuesTest extends AbstractUnitTestCase
{
    /**
     * cphalcon gives a warning and does not set a property with a numeric
     * name
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2026-11-20
     */
    public function testFormsFormBindNumericDataKey(): void
    {
        $form = new Form();
        $form->add(new Text('1'));

        $entity   = new stdClass();
        $warnings = [];
        set_error_handler(
            function (int $number, string $message) use (&$warnings): bool {
                $warnings[] = $message;

                return true;
            }
        );

        try {
            $form->bind(['1' => 'x'], $entity);
        } finally {
            restore_error_handler();
        }

        $this->assertCount(1, $warnings);
        $this->assertStringContainsString('Property should be string', $warnings[0]);
        $this->assertSame([], (array) $entity);
    }

    /**
     * @author Phalcon Team <team@phalcon.io>
     * @since  2026-11-20
     */
    public function testFormsFormGetValueEntityInt(): void
    {
        $form = new Form();
        $form->add(new Text('x'));
        $form->setEntity(123);

        $this->assertNull($form->getValue('x'));
    }
}
