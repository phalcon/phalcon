<?php

/**
 * This file is part of the Phalcon Framework.
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Phalcon\Html;

use Exception as BaseException;
use Phalcon\Html\Escaper\EscaperInterface;
use Phalcon\Html\Helper\Anchor;
use Phalcon\Html\Helper\Base;
use Phalcon\Html\Helper\Body;
use Phalcon\Html\Helper\Breadcrumbs;
use Phalcon\Html\Helper\Button;
use Phalcon\Html\Helper\Close;
use Phalcon\Html\Helper\Doctype;
use Phalcon\Html\Helper\Element;
use Phalcon\Html\Helper\Form;
use Phalcon\Html\Helper\Img;
use Phalcon\Html\Helper\Input\Checkbox;
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
use Phalcon\Html\Helper\Input\Radio;
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
use Phalcon\Traits\Factory\FactoryTrait;

use function call_user_func_array;

/**
 * ServiceLocator implementation for Tag helpers.
 *
 * Services are registered using the constructor using a key-value pair. The
 * key is the name of the tag helper, while the value is a callable that returns
 * the object.
 *
 * The class implements `__call()` to allow calling helper objects as methods.
 *
 * @property EscaperInterface $escaper
 * @property array            $services
 *
 * @method string        a(string $href, string $text, array $attributes = [], bool $raw = false)
 * @method string        base(string $href, array $attributes = [])
 * @method string        body(array $attributes = [])
 * @method Breadcrumbs   breadcrumbs(string $indent = '    ', string $delimiter = "\n")
 * @method string        button(string $text, array $attributes = [], bool $raw = false)
 * @method string        close(string $tag, bool $raw = false)
 * @method Doctype       doctype(int $flag, string $delimiter)
 * @method string        element(string $tag, string $text, array $attributes = [], bool $raw = false)
 * @method string        form(array $attributes = [])
 * @method string        img(string $src, array $attributes = [])
 * @method Checkbox      inputCheckbox(string $name, string $value = null, array $attributes = [])
 * @method Color         inputColor(string $name, string $value = null, array $attributes = [])
 * @method Date          inputDate(string $name, string $value = null, array $attributes = [])
 * @method DateTime      inputDateTime(string $name, string $value = null, array $attributes = [])
 * @method DateTimeLocal inputDateTimeLocal(string $name, string $value = null, array $attributes = [])
 * @method Email         inputEmail(string $name, string $value = null, array $attributes = [])
 * @method File          inputFile(string $name, string $value = null, array $attributes = [])
 * @method Hidden        inputHidden(string $name, string $value = null, array $attributes = [])
 * @method Image         inputImage(string $name, string $value = null, array $attributes = [])
 * @method Input         inputInput(string $name, string $value = null, array $attributes = [])
 * @method Month         inputMonth(string $name, string $value = null, array $attributes = [])
 * @method Numeric       inputNumeric(string $name, string $value = null, array $attributes = [])
 * @method Password      inputPassword(string $name, string $value = null, array $attributes = [])
 * @method Radio         inputRadio(string $name, string $value = null, array $attributes = [])
 * @method Range         inputRange(string $name, string $value = null, array $attributes = [])
 * @method Search        inputSearch(string $name, string $value = null, array $attributes = [])
 * @method Select        inputSelect(string $name, string $value = null, array $attributes = [])
 * @method Submit        inputSubmit(string $name, string $value = null, array $attributes = [])
 * @method Tel           inputTel(string $name, string $value = null, array $attributes = [])
 * @method Text          inputText(string $name, string $value = null, array $attributes = [])
 * @method Textarea      inputTextarea(string $name, string $value = null, array $attributes = [])
 * @method Time          inputTime(string $name, string $value = null, array $attributes = [])
 * @method Url           inputUrl(string $name, string $value = null, array $attributes = [])
 * @method Week          inputWeek(string $name, string $value = null, array $attributes = [])
 * @method string        label(string $label, array $attributes = [], bool $raw = false)
 * @method Link          link(string $indent = '    ', string $delimiter = "\n")
 * @method Meta          meta(string $indent = '    ', string $delimiter = "\n")
 * @method Ol            ol(string $text, array $attributes = [], bool $raw = false)
 * @method Script        script(string $indent = '    ', string $delimiter = "\n")
 * @method Style         style(string $indent = '    ', string $delimiter = "\n")
 * @method Title         title(string $indent = '    ', string $delimiter = "\n")
 * @method Ul            ul(string $text, array $attributes = [], bool $raw = false)
 */
class TagFactory
{
    use FactoryTrait;

    /**
     * @var EscaperInterface
     */
    private EscaperInterface $escaper;

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
     * Magic call to make the helper objects available as methods.
     *
     * @param string $name
     * @param array  $args
     *
     * @return false|mixed
     * @throws Exception
     */
    public function __call(string $name, array $args)
    {
        $services = $this->getServices();

        if (!isset($services[$name])) {
            throw new Exception('Service ' . $name . ' is not registered');
        }

        return call_user_func_array($this->newInstance($name), $args);
    }

    /**
     * @param string $name
     *
     * @return bool
     */
    public function has(string $name): bool
    {
        return isset($this->mapper[$name]);
    }

    /**
     * Create a new instance of the object
     *
     * @param string $name
     *
     * @return mixed
     * @throws BaseException
     */
    public function newInstance(string $name)
    {
        return $this->getCachedInstance($name, $this->escaper);
    }

    /**
     * @param string   $name
     * @param callable $callable
     */
    public function set(string $name, $callable): void
    {
        $this->mapper[$name] = $callable;
        unset($this->instances[$name]);
    }

    /**
     * @return string
     */
    protected function getExceptionClass(): string
    {
        return Exception::class;
    }

    /**
     * Returns the available services
     *
     * @return string[]
     */
    protected function getServices(): array
    {
        return [
            'a'                  => Anchor::class,
            'base'               => Base::class,
            'breadcrumbs'        => Breadcrumbs::class,
            'body'               => Body::class,
            'button'             => Button::class,
            'close'              => Close::class,
            'doctype'            => Doctype::class,
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
