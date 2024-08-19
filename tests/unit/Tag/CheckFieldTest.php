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

namespace Phalcon\Tests\Unit\Tag;

use PHPUnit\Framework\Attributes\Test;

class CheckFieldTest extends AbstractTagSetup
{
    /**
     * Tests Phalcon\Tag :: weekField() - string as a parameter
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2014-09-05
     */
    #[Test]
    public function testTagFieldStringParameter()
    {
        $options  = 'x_name';
        $expected = '<input type="checkbox" id="x_name" name="x_name"';

        $this->testFieldParameter('checkField', $options, $expected);
        $this->testFieldParameter('checkField', $options, $expected, true);
    }

    /**
     * Tests Phalcon\Tag :: weekField() - array as a parameter
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2014-09-05
     */
    #[Test]
    public function testTagFieldArrayParameter()
    {
        $options = [
            'x_name',
            'class' => 'x_class',
        ];

        $expected = '<input type="checkbox" id="x_name" name="x_name" class="x_class"';

        $this->testFieldParameter('checkField', $options, $expected);
        $this->testFieldParameter('checkField', $options, $expected, true);
    }

    /**
     * Tests Phalcon\Tag :: weekField() - array as a parameters and id in it
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2014-09-05
     */
    #[Test]
    public function testTagFieldArrayParameterWithId()
    {
        $options = [
            'x_name',
            'id'    => 'x_id',
            'class' => 'x_class',
            'size'  => '10',
        ];

        $expected = '<input type="checkbox" id="x_id" name="x_name" '
            . 'class="x_class" size="10"';

        $this->testFieldParameter('checkField', $options, $expected);
        $this->testFieldParameter('checkField', $options, $expected, true);
    }

    /**
     * Tests Phalcon\Tag :: weekField() - name and no id in parameter
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2014-09-05
     */
    #[Test]
    public function testTagFieldArrayParameterWithNameNoId()
    {
        $options = [
            'x_name',
            'name'  => 'x_other',
            'class' => 'x_class',
            'size'  => '10',
        ];

        $expected = '<input type="checkbox" id="x_name" name="x_other" class="x_class" size="10"';

        $this->testFieldParameter('checkField', $options, $expected);
        $this->testFieldParameter('checkField', $options, $expected, true);
    }

    /**
     * Tests Phalcon\Tag :: weekField() - displayTo
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2014-09-05
     */
    #[Test]
    public function testTagFieldWithDisplayTo(): void
    {
        $options = [
            'x_name',
            'name'  => 'x_other',
            'class' => 'x_class',
            'size'  => '10',
        ];

        $expected = '<input type="checkbox" id="x_name" '
            . 'name="x_other" value="x_value" class="x_class" size="10" checked="checked"';

        $this->testFieldParameter('checkField', $options, $expected, false, 'displayTo');
        $this->testFieldParameter('checkField', $options, $expected, true, 'displayTo');
    }

    /**
     * Tests Phalcon\Tag :: weekField() - displayTo and element not present
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2014-09-05
     */
    #[Test]
    public function testTagFieldWithDisplayToElementNotPresent(): void
    {
        $options = [
            'x_name',
            'name'  => 'x_other',
            'class' => 'x_class',
            'size'  => '10',
        ];

        $expected = '<input type="checkbox" id="x_name" '
            . 'name="x_other" value="x_value" class="x_class" size="10" checked="checked"';

        $this->testFieldParameter('checkField', $options, $expected, false, 'displayTo');
        $this->testFieldParameter('checkField', $options, $expected, true, 'displayTo');
    }

    /**
     * Tests Phalcon\Tag :: weekField() - setDefault
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2014-09-05
     */
    #[Test]
    public function testTagFieldWithSetDefault(): void
    {
        $options = [
            'x_name',
            'name'  => 'x_other',
            'class' => 'x_class',
            'size'  => '10',
        ];

        $expected = '<input type="checkbox" id="x_name" '
            . 'name="x_other" value="x_value" class="x_class" size="10" checked="checked"';

        $this->testFieldParameter('checkField', $options, $expected, false, 'setDefault');
        $this->testFieldParameter('checkField', $options, $expected, true, 'setDefault');
    }

    /**
     * Tests Phalcon\Tag :: weekField() - setDefault and element not present
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2014-09-05
     */
    #[Test]
    public function testTagFieldWithSetDefaultElementNotPresent(): void
    {
        $options = [
            'x_name',
            'name'  => 'x_other',
            'class' => 'x_class',
            'size'  => '10',
        ];

        $expected = '<input type="checkbox" id="x_name" '
            . 'name="x_other" value="x_value" class="x_class" size="10" checked="checked"';

        $this->testFieldParameter('checkField', $options, $expected, false, 'setDefault');
        $this->testFieldParameter('checkField', $options, $expected, true, 'setDefault');
    }
}
