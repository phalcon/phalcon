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

namespace Phalcon\Tests\Integration\Forms\Element;

use Codeception\Example;
use IntegrationTester;
use Phalcon\Forms\Element\Check;
use Phalcon\Forms\Element\Date;
use Phalcon\Forms\Element\Email;
use Phalcon\Forms\Element\File;
use Phalcon\Forms\Element\Hidden;
use Phalcon\Forms\Element\Numeric;
use Phalcon\Forms\Element\Password;
use Phalcon\Forms\Element\Radio;
use Phalcon\Forms\Element\Select;
use Phalcon\Forms\Element\Submit;
use Phalcon\Forms\Element\Text;
use Phalcon\Forms\Element\TextArea;
use Phalcon\Html\Escaper;
use Phalcon\Html\TagFactory;

use function uniqid;

class RenderCest
{
    /**
     * Tests Phalcon\Forms\Element\* :: clear()
     *
     * @dataProvider getExamples
     *
     * @param IntegrationTester $I
     * @param Example           $example
     *
     * @author       Phalcon Team <team@phalcon.io>
     * @since        2021-12-05
     */
    public function formsElementRender(IntegrationTester $I, Example $example)
    {
        $I->wantToTest('Forms\Element\* - render() - ' . $example[0]);

        $name    = uniqid();
        $class   = $example[1];
        $factory = new TagFactory(new Escaper());
        $object  = new $class($name);
        $object->setTagFactory($factory);

        $expected = str_replace(":name:", $name, $example[2]);
        $actual   = $object->render();

        $I->assertSame($expected, $actual);

        $actual = (string)$object;
        $I->assertSame($expected, $actual);

        /**
         * With attributes
         */
        $object->setAttributes($example[3]);

        $expected = str_replace(":name:", $name, $example[4]);
        $actual   = $object->render();

        $I->assertSame($expected, $actual);

        $actual = (string)$object;
        $I->assertSame($expected, $actual);
    }

    /**
     * @return string[][]
     */
    private function getExamples(): array
    {
        $htmlLine   = 'id=":name:" name=":name:" class="alert alert-warning"';
        $attributes = ['class' => 'alert alert-warning'];
        return [
            [
                "Check",
                Check::class,
                '<input type="checkbox" id=":name:" name=":name:" />',
                $attributes,
                '<input type="checkbox" ' . $htmlLine . ' />',
            ],
            [
                "Date",
                Date::class,
                '<input type="date" id=":name:" name=":name:" />',
                $attributes,
                '<input type="date" ' . $htmlLine . ' />',
            ],
            [
                "Email",
                Email::class,
                '<input type="email" id=":name:" name=":name:" />',
                $attributes,
                '<input type="email" ' . $htmlLine . ' />',
            ],
            [
                "File",
                File::class,
                '<input type="file" id=":name:" name=":name:" />',
                $attributes,
                '<input type="file" ' . $htmlLine . ' />',
            ],
            [
                "Hidden",
                Hidden::class,
                '<input type="hidden" id=":name:" name=":name:" />',
                $attributes,
                '<input type="hidden" ' . $htmlLine . ' />',
            ],
            [
                "Numeric",
                Numeric::class,
                '<input type="number" id=":name:" name=":name:" />',
                $attributes,
                '<input type="number" ' . $htmlLine . ' />',
            ],
            [
                "Password",
                Password::class,
                '<input type="password" id=":name:" name=":name:" />',
                $attributes,
                '<input type="password" ' . $htmlLine . ' />',
            ],
            [
                "Radio",
                Radio::class,
                '<input type="radio" id=":name:" name=":name:" />',
                $attributes,
                '<input type="radio" ' . $htmlLine . ' />',
            ],
            [
                "Select",
                Select::class,
                '<select id=":name:" name=":name:">' . PHP_EOL . '</select>',
                $attributes,
                '<select ' . $htmlLine . '>' . PHP_EOL . '</select>',
            ],
            [
                "Submit",
                Submit::class,
                '<input type="submit" id=":name:" name=":name:" />',
                $attributes,
                '<input type="submit" ' . $htmlLine . ' />',
            ],
            [
                "Text",
                Text::class,
                '<input type="text" id=":name:" name=":name:" />',
                $attributes,
                '<input type="text" ' . $htmlLine . ' />',
            ],
            [
                "TextArea",
                TextArea::class,
                '<textarea id=":name:" name=":name:"></textarea>',
                $attributes,
                '<textarea ' . $htmlLine . '></textarea>',
            ],
        ];
    }
}
