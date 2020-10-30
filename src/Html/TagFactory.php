<?php

/**
 * This file is part of the Phalcon Framework.
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Phalcon\Html;

use Phalcon\Html\Helper\Anchor;
use Phalcon\Html\Helper\Base;
use Phalcon\Html\Helper\Body;
use Phalcon\Html\Helper\Button;
use Phalcon\Html\Helper\Close;
use Phalcon\Html\Helper\Element;
use Phalcon\Html\Helper\Form;
use Phalcon\Html\Helper\Img;
use Phalcon\Html\Helper\Input\Color;
use Phalcon\Html\Helper\Input\Date;
use Phalcon\Html\Helper\Input\DateTime;
use Phalcon\Html\Helper\Input\DateTimeLocal;
use Phalcon\Html\Helper\Input\Email;
use Phalcon\Html\Helper\Input\File;
use Phalcon\Html\Helper\Input\Hidden;
use Phalcon\Html\Helper\Input\Image;
use Phalcon\Html\Helper\Input\Input;
use Phalcon\Html\Helper\Input\Month;
use Phalcon\Html\Helper\Input\Numeric;
use Phalcon\Html\Helper\Input\Password;
use Phalcon\Html\Helper\Input\Range;
use Phalcon\Html\Helper\Input\Search;
use Phalcon\Html\Helper\Input\Select;
use Phalcon\Html\Helper\Input\Submit;
use Phalcon\Html\Helper\Input\Tel;
use Phalcon\Html\Helper\Input\Text;
use Phalcon\Html\Helper\Input\Textarea;
use Phalcon\Html\Helper\Input\Time;
use Phalcon\Html\Helper\Input\Url;
use Phalcon\Html\Helper\Input\Week;
use Phalcon\Html\Helper\Label;
use Phalcon\Html\Helper\Link;
use Phalcon\Html\Helper\Meta;
use Phalcon\Html\Helper\Ol;
use Phalcon\Html\Helper\Script;
use Phalcon\Html\Helper\Style;
use Phalcon\Html\Helper\Title;
use Phalcon\Html\Helper\Ul;
use Phalcon\Support\Traits\FactoryTrait;

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
            'inputRange'         => Range::class,
            'inputSelect'        => Select::class,
            'inputSearch'        => Search::class,
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
