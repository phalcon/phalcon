<?php

/**
 * This file is part of the Phalcon Framework.
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Phiz\Html;

use Phiz\Html\Tag\Anchor;
use Phiz\Html\Tag\Base;
use Phiz\Html\Tag\Body;
use Phiz\Html\Tag\Button;
use Phiz\Html\Tag\Close;
use Phiz\Html\Tag\Element;
use Phiz\Html\Tag\Form;
use Phiz\Html\Tag\Img;
use Phiz\Html\Tag\Input\Checkbox;
use Phiz\Html\Tag\Input\Color;
use Phiz\Html\Tag\Input\Date;
use Phiz\Html\Tag\Input\DateTime;
use Phiz\Html\Tag\Input\DateTimeLocal;
use Phiz\Html\Tag\Input\Email;
use Phiz\Html\Tag\Input\File;
use Phiz\Html\Tag\Input\Hidden;
use Phiz\Html\Tag\Input\Image;
use Phiz\Html\Tag\Input\Input;
use Phiz\Html\Tag\Input\Month;
use Phiz\Html\Tag\Input\Numeric;
use Phiz\Html\Tag\Input\Password;
use Phiz\Html\Tag\Input\Radio;
use Phiz\Html\Tag\Input\Range;
use Phiz\Html\Tag\Input\Search;
use Phiz\Html\Tag\Input\Select;
use Phiz\Html\Tag\Input\Submit;
use Phiz\Html\Tag\Input\Tel;
use Phiz\Html\Tag\Input\Text;
use Phiz\Html\Tag\Input\Textarea;
use Phiz\Html\Tag\Input\Time;
use Phiz\Html\Tag\Input\Url;
use Phiz\Html\Tag\Input\Week;
use Phiz\Html\Tag\Label;
use Phiz\Html\Tag\Link;
use Phiz\Html\Tag\Meta;
use Phiz\Html\Tag\Ol;
use Phiz\Html\Tag\Script;
use Phiz\Html\Tag\Style;
use Phiz\Html\Tag\Title;
use Phiz\Html\Tag\Ul;
use Phiz\Support\Traits\FactoryTrait;

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
