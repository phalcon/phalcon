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

namespace Phalcon\Tests\Fixtures\Helpers;

class TagHelper extends TagSetup
{
    /**
     * @var string
     */
    protected string $function = '';

    /**
     * @var string
     */
    protected string $inputType = '';

    /**
     * Tests Phalcon\Tag :: weekField() - string as a parameter
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2014-09-05
     */
    public function testTagFieldStringParameter()
    {
        $options  = 'x_name';
        $expected = '<input type="' . $this->inputType . '" id="x_name" name="x_name"';

        $this->testFieldParameter($this->function, $options, $expected);
        $this->testFieldParameter($this->function, $options, $expected, true);
    }

    /**
     * Tests Phalcon\Tag :: weekField() - array as a parameter
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2014-09-05
     */
    public function testTagFieldArrayParameter()
    {
        $options = [
            'x_name',
            'class' => 'x_class',
        ];

        $expected = '<input type="' . $this->inputType . '" id="x_name" name="x_name" class="x_class"';

        $this->testFieldParameter($this->function, $options, $expected);
        $this->testFieldParameter($this->function, $options, $expected, true);
    }

    /**
     * Tests Phalcon\Tag :: weekField() - array as a parameters and id in it
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2014-09-05
     */
    public function testTagFieldArrayParameterWithId()
    {
        $options = [
            'x_name',
            'id'    => 'x_id',
            'class' => 'x_class',
            'size'  => '10',
        ];

        $expected = '<input type="' . $this->inputType . '" id="x_id" name="x_name" '
            . 'class="x_class" size="10"';

        $this->testFieldParameter($this->function, $options, $expected);
        $this->testFieldParameter($this->function, $options, $expected, true);
    }

    /**
     * Tests Phalcon\Tag :: weekField() - name and no id in parameter
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2014-09-05
     */
    public function testTagFieldArrayParameterWithNameNoId()
    {
        $options = [
            'x_name',
            'name'  => 'x_other',
            'class' => 'x_class',
            'size'  => '10',
        ];

        $expected = '<input type="' . $this->inputType . '" id="x_name" name="x_other" class="x_class" size="10"';

        $this->testFieldParameter($this->function, $options, $expected);
        $this->testFieldParameter($this->function, $options, $expected, true);
    }

    /**
     * Tests Phalcon\Tag :: weekField() - setDefault
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2014-09-05
     */
    public function testTagFieldWithSetDefault()
    {
        $options = [
            'x_name',
            'name'  => 'x_other',
            'class' => 'x_class',
            'size'  => '10',
        ];

        $expected = '<input type="' . $this->inputType . '" id="x_name" '
            . 'name="x_other" value="x_value" class="x_class" size="10"';

        $this->testFieldParameter($this->function, $options, $expected, false, 'setDefault');
        $this->testFieldParameter($this->function, $options, $expected, true, 'setDefault');
    }

    /**
     * Tests Phalcon\Tag :: weekField() - displayTo
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2014-09-05
     */
    public function testTagFieldWithDisplayTo()
    {
        $options = [
            'x_name',
            'name'  => 'x_other',
            'class' => 'x_class',
            'size'  => '10',
        ];

        $expected = '<input type="' . $this->inputType . '" id="x_name" '
            . 'name="x_other" value="x_value" class="x_class" size="10"';

        $this->testFieldParameter($this->function, $options, $expected, false, 'displayTo');
        $this->testFieldParameter($this->function, $options, $expected, true, 'displayTo');
    }

    /**
     * Tests Phalcon\Tag :: weekField() - setDefault and element not present
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2014-09-05
     */
    public function testTagFieldWithSetDefaultElementNotPresent()
    {
        $options = [
            'x_name',
            'name'  => 'x_other',
            'class' => 'x_class',
            'size'  => '10',
        ];

        $expected = '<input type="' . $this->inputType . '" id="x_name" '
            . 'name="x_other" value="x_value" class="x_class" size="10"';

        $this->testFieldParameter($this->function, $options, $expected, false, 'setDefault');
        $this->testFieldParameter($this->function, $options, $expected, true, 'setDefault');
    }

    /**
     * Tests Phalcon\Tag :: weekField() - displayTo and element not present
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2014-09-05
     */
    public function testTagFieldWithDisplayToElementNotPresent()
    {
        $options = [
            'x_name',
            'name'  => 'x_other',
            'class' => 'x_class',
            'size'  => '10',
        ];

        $expected = '<input type="' . $this->inputType . '" id="x_name" '
            . 'name="x_other" value="x_value" class="x_class" size="10"';

        $this->testFieldParameter($this->function, $options, $expected, false, 'displayTo');
        $this->testFieldParameter($this->function, $options, $expected, true, 'displayTo');
    }
}
