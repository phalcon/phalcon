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

namespace Phalcon\Tests\Unit\Html\TagFactory;

use Codeception\Example;
use Phalcon\Html\Escaper;
use Phalcon\Html\Tag\Anchor;
use Phalcon\Html\Tag\Base;
use Phalcon\Html\Tag\Body;
use Phalcon\Html\Tag\Button;
use Phalcon\Html\Tag\Close;
use Phalcon\Html\Tag\Element;
use Phalcon\Html\Tag\Form;
use Phalcon\Html\Tag\Img;
use Phalcon\Html\Tag\Input\Color;
use Phalcon\Html\Tag\Input\Date;
use Phalcon\Html\Tag\Input\DateTime;
use Phalcon\Html\Tag\Input\DateTimeLocal;
use Phalcon\Html\Tag\Input\Email;
use Phalcon\Html\Tag\Input\File;
use Phalcon\Html\Tag\Input\Hidden;
use Phalcon\Html\Tag\Input\Image;
use Phalcon\Html\Tag\Input\Input;
use Phalcon\Html\Tag\Input\Month;
use Phalcon\Html\Tag\Input\Numeric;
use Phalcon\Html\Tag\Input\Password;
use Phalcon\Html\Tag\Input\Range;
use Phalcon\Html\Tag\Input\Search;
use Phalcon\Html\Tag\Input\Select;
use Phalcon\Html\Tag\Input\Submit;
use Phalcon\Html\Tag\Input\Tel;
use Phalcon\Html\Tag\Input\Text;
use Phalcon\Html\Tag\Input\Textarea;
use Phalcon\Html\Tag\Input\Time;
use Phalcon\Html\Tag\Input\Url;
use Phalcon\Html\Tag\Input\Week;
use Phalcon\Html\Tag\Label;
use Phalcon\Html\Tag\Link;
use Phalcon\Html\Tag\Meta;
use Phalcon\Html\Tag\Ol;
use Phalcon\Html\Tag\Script;
use Phalcon\Html\Tag\Style;
use Phalcon\Html\Tag\Title;
use Phalcon\Html\Tag\Ul;
use Phalcon\Html\TagFactory;
use Phalcon\Support\Exception as SupportException;
use UnitTester;

/**
 * Class NewInstanceCest
 *
 * @package Phalcon\Tests\Unit\Html\TagFactory
 */
class NewInstanceCest
{
    /**
     * Tests Phalcon\Tag\TagFactory :: newInstance() - services
     *
     * @dataProvider getData
     *
     * @param UnitTester $I
     * @param Example    $example
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2020-09-09
     */
    public function filterTagFactoryNewInstanceServices(UnitTester $I, Example $example)
    {
        $I->wantToTest('Tag\TagFactory - newInstance() - services ' . $example[0]);
        $escaper = new Escaper();
        $factory = new TagFactory($escaper);
        $service = $factory->newInstance($example[0]);

        $class = $example[1];
        $I->assertInstanceOf($class, $service);
    }

    /**
     * Tests Phalcon\Storage\SerializerFactory :: newInstance() - exception
     *
     * @param UnitTester $I
     *
     * @author Phalcon Team <team@phalcon.io>
     * @since  2020-09-09
     */
    public function filterTagFactoryNewInstanceException(UnitTester $I)
    {
        $I->wantToTest('Tag\TagFactory - newInstance() - exception');

        $I->expectThrowable(
            new SupportException('Service unknown is not registered'),
            function () {
                $escaper = new Escaper();
                $factory = new TagFactory($escaper);
                $service = $factory->newInstance('unknown');
            }
        );
    }

    /**
     * Returns the example data
     */
    private function getData(): array
    {
        return [
            ["a", Anchor::class],
            ["base", Base::class],
            ["body", Body::class],
            ["button", Button::class],
            ["close", Close::class],
            ["element", Element::class],
            ["form", Form::class],
            ["img", Img::class],
            ["inputColor", Color::class],
            ["inputDate", Date::class],
            ["inputDateTime", DateTime::class],
            ["inputDateTimeLocal", DateTimeLocal::class],
            ["inputEmail", Email::class],
            ["inputFile", File::class],
            ["inputHidden", Hidden::class],
            ["inputImage", Image::class],
            ["inputInput", Input::class],
            ["inputMonth", Month::class],
            ["inputNumeric", Numeric::class],
            ["inputPassword", Password::class],
            ["inputRange", Range::class],
            ["inputSelect", Select::class],
            ["inputSearch", Search::class],
            ["inputSubmit", Submit::class],
            ["inputTel", Tel::class],
            ["inputText", Text::class],
            ["inputTextarea", Textarea::class],
            ["inputTime", Time::class],
            ["inputUrl", Url::class],
            ["inputWeek", Week::class],
            ["label", Label::class],
            ["link", Link::class],
            ["meta", Meta::class],
            ["ol", Ol::class],
            ["script", Script::class],
            ["style", Style::class],
            ["title", Title::class],
            ["ul", Ul::class],
        ];
    }
}
