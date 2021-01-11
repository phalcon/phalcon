<?php

/**
 * This file is part of the Phalcon Framework.
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Phalcon\Html;

use Phalcon\Html\Tag\Anchor;
use Phalcon\Html\Tag\Base;
use Phalcon\Html\Tag\Body;
use Phalcon\Html\Tag\Button;
use Phalcon\Html\Tag\Close;
use Phalcon\Html\Tag\Element;
use Phalcon\Html\Tag\Form;
use Phalcon\Html\Tag\Img;
use Phalcon\Html\Tag\Input\Checkbox;
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
use Phalcon\Html\Tag\Input\Radio;
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
use Phalcon\Support\Traits\FactoryTrait;

use function call_user_func_array;

/**
 * ServiceLocator implementation for Tag helpers
 *
 * @property EscaperInterface $escaper
 * @property array            $services
 */
class TagFactory
{
    use FactoryTrait;

    /**
     * @var EscaperInterface
     */
    private EscaperInterface $escaper;

    /**
     * @var array
     */
    protected array $services = [];

    /**
     * TagFactory constructor.
     */
    /**
     * TagFactory constructor.
     *
     * @param Escaper $escaper
     * @param array   $services
     */
    public function __construct(EscaperInterface $escaper, array $services = [])
    {
        $this->escaper = $escaper;

        $this->init($services);
    }

    /**
     * Create a new instance of the object
     *
     * @param string $name
     *
     * @return mixed
     */
    public function newInstance(string $name)
    {
        if (true !== isset($this->services[$name])) {
            $definition            = $this->getService($name);
            $this->services[$name] = new $definition($this->escaper);
        }

        return $this->services[$name];
    }

    /**
     * @return array
     */
    protected function getServices(): array
    {
        return [
            'a'                  => Anchor::class,
            'base'               => Base::class,
            'body'               => Body::class,
            'button'             => Button::class,
            'close'              => Close::class,
            'element'            => Element::class,
            'form'               => Form::class,
            'img'                => Img::class,
            'inputCheckbox'      => Checkbox::class,
            'inputColor'         => Color::class,
            'inputDate'          => Date::class,
            'inputDateTime'      => DateTime::class,
            'inputDateTimeLocal' => DateTimeLocal::class,
            'inputEmail'         => Email::class,
            'inputFile'          => File::class,
            'inputHidden'        => Hidden::class,
            'inputImage'         => Image::class,
            'inputInput'         => Input::class,
            'inputMonth'         => Month::class,
            'inputNumeric'       => Numeric::class,
            'inputPassword'      => Password::class,
            'inputRadio'         => Radio::class,
            'inputRange'         => Range::class,
            'inputSearch'        => Search::class,
            'inputSelect'        => Select::class,
            'inputSubmit'        => Submit::class,
            'inputTel'           => Tel::class,
            'inputText'          => Text::class,
            'inputTextarea'      => Textarea::class,
            'inputTime'          => Time::class,
            'inputUrl'           => Url::class,
            'inputWeek'          => Week::class,
            'label'              => Label::class,
            'link'               => Link::class,
            'meta'               => Meta::class,
            'ol'                 => Ol::class,
            'script'             => Script::class,
            'style'              => Style::class,
            'title'              => Title::class,
            'ul'                 => Ul::class,
        ];
    }
}
