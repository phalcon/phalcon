<?php

/**
 * This file is part of the Phalcon Framework.
 *
 * For the full copyright and license information, please view the LICENSE.md
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Phalcon\Html;

use Phalcon\Html\Escaper\EscaperInterface;
use Phalcon\Html\Helper\Anchor;
use Phalcon\Html\Helper\Base;
use Phalcon\Html\Helper\Body;
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

use const PHP_EOL;

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
 * @method string a(string $href, string $text, array $attributes = [], bool $raw = false)
 * @method string base(string $href, array $attributes = [])
 * @method string body(array $attributes = [])
 * @method string button(string $text, array $attributes = [], bool $raw = false)
 * @method string close(string $tag, bool $raw = false)
 * @method string doctype(int $flag, string $delimiter)
 * @method string element(string $tag, string $text, array $attributes = [], bool $raw = false)
 * @method string form(array $attributes = [])
 * @method string img(string $src, array $attributes = [])
 * @method string inputCheckbox(string $name, string $value = null, array $attributes = [])
 * @method string inputColor(string $name, string $value = null, array $attributes = [])
 * @method string inputDate(string $name, string $value = null, array $attributes = [])
 * @method string inputDateTime(string $name, string $value = null, array $attributes = [])
 * @method string inputDateTimeLocal(string $name, string $value = null, array $attributes = [])
 * @method string inputEmail(string $name, string $value = null, array $attributes = [])
 * @method string inputFile(string $name, string $value = null, array $attributes = [])
 * @method string inputHidden(string $name, string $value = null, array $attributes = [])
 * @method string inputImage(string $name, string $value = null, array $attributes = [])
 * @method string inputInput(string $name, string $value = null, array $attributes = [])
 * @method string inputMonth(string $name, string $value = null, array $attributes = [])
 * @method string inputNumeric(string $name, string $value = null, array $attributes = [])
 * @method string inputPassword(string $name, string $value = null, array $attributes = [])
 * @method string inputRadio(string $name, string $value = null, array $attributes = [])
 * @method string inputRange(string $name, string $value = null, array $attributes = [])
 * @method string inputSearch(string $name, string $value = null, array $attributes = [])
 * @method string inputSelect(string $name, string $value = null, array $attributes = [])
 * @method string inputSubmit(string $name, string $value = null, array $attributes = [])
 * @method string inputTel(string $name, string $value = null, array $attributes = [])
 * @method string inputText(string $name, string $value = null, array $attributes = [])
 * @method string inputTextarea(string $name, string $value = null, array $attributes = [])
 * @method string inputTime(string $name, string $value = null, array $attributes = [])
 * @method string inputUrl(string $name, string $value = null, array $attributes = [])
 * @method string inputWeek(string $name, string $value = null, array $attributes = [])
 * @method string label(string $label, array $attributes = [], bool $raw = false)
 * @method string link(string $indent = '    ', string $delimiter = PHP_EOL)
 * @method string meta(string $indent = '    ', string $delimiter = PHP_EOL)
 * @method string ol(string $text, array $attributes = [], bool $raw = false)
 * @method string script(string $indent = '    ', string $delimiter = PHP_EOL)
 * @method string style(string $indent = '    ', string $delimiter = PHP_EOL)
 * @method string title(string $indent = '    ', string $delimiter = PHP_EOL)
 * @method string ul(string $text, array $attributes = [], bool $raw = false)
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
     */
    public function __call(string $name, array $args)
    {
        $services = $this->getServices();

        if (true !== isset($services[$name])) {
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
     * @param string   $name
     * @param callable $callable
     */
    public function set(string $name, $callable): void
    {
        $this->mapper[$name] = $callable;
        unset($this->services[$name]);
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
