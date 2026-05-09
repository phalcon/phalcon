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
use Phalcon\Html\Helper\Breadcrumbs;
use Phalcon\Html\Helper\Button;
use Phalcon\Html\Helper\Close;
use Phalcon\Html\Helper\Doctype;
use Phalcon\Html\Helper\Element;
use Phalcon\Html\Helper\Form;
use Phalcon\Html\Helper\FriendlyTitle;
use Phalcon\Html\Helper\Img;
use Phalcon\Html\Helper\Input\Checkbox;
use Phalcon\Html\Helper\Input\Generic;
use Phalcon\Html\Helper\Input\Radio;
use Phalcon\Html\Helper\Input\Select;
use Phalcon\Html\Helper\Input\Textarea;
use Phalcon\Html\Helper\Label;
use Phalcon\Html\Helper\Link;
use Phalcon\Html\Helper\Meta;
use Phalcon\Html\Helper\Ol;
use Phalcon\Html\Helper\Preload;
use Phalcon\Html\Helper\Script;
use Phalcon\Html\Helper\Style;
use Phalcon\Html\Helper\Tag;
use Phalcon\Html\Helper\Title;
use Phalcon\Html\Helper\Ul;
use Phalcon\Html\Helper\VoidTag;
use Phalcon\Http\ResponseInterface;
use Phalcon\Mvc\Url\UrlInterface;

use function call_user_func_array;

/**
 * ServiceLocator implementation for Tag helpers.
 *
 * Built-in services are seeded by the constructor. Users may add or override
 * services via `set()`, passing a callable that returns the helper instance.
 *
 * Helpers are cached per name after first construction.
 *
 * `__call()` resolves the named helper and dispatches to its `__invoke()`,
 * so each entry in the @method block below describes the result of calling
 * `$factory->serviceName(...)` rather than `newInstance("serviceName")`.
 *
 * @property EscaperInterface       $escaper
 * @property ResponseInterface|null $response
 * @property UrlInterface|null      $url
 * @property array                  $factories
 * @property array                  $instances
 *
 * @method string      a(string $href, string $text, array $attributes = [], bool $raw = false)
 * @method string      aRaw(string $href, string $text, array $attributes = [])
 * @method string      base(string $href, array $attributes = [])
 * @method string      body(array $attributes = [])
 * @method Breadcrumbs breadcrumbs(string $indent = '    ', string $delimiter = "\n")
 * @method string      button(string $text, array $attributes = [], bool $raw = false)
 * @method string      buttonRaw(string $text, array $attributes = [])
 * @method string      close(string $tag, bool $raw = false)
 * @method Doctype     doctype(int $type = Doctype::HTML5, string $delimiter = "\n")
 * @method string      element(string $tag, string $text, array $attributes = [], bool $raw = false)
 * @method string      elementRaw(string $tag, string $text, array $attributes = [])
 * @method string      form(array $attributes = [])
 * @method string      friendlyTitle(string $text, string $separator = '-', bool $lowercase = true, mixed $replace = null)
 * @method string      img(string $src, array $attributes = [])
 * @method Checkbox    inputCheckbox(string $name, string $value = null, array $attributes = [])
 * @method Generic     inputColor(string $name, string $value = null, array $attributes = [])
 * @method Generic     inputDate(string $name, string $value = null, array $attributes = [])
 * @method Generic     inputDateTime(string $name, string $value = null, array $attributes = [])
 * @method Generic     inputDateTimeLocal(string $name, string $value = null, array $attributes = [])
 * @method Generic     inputEmail(string $name, string $value = null, array $attributes = [])
 * @method Generic     inputFile(string $name, string $value = null, array $attributes = [])
 * @method Generic     inputHidden(string $name, string $value = null, array $attributes = [])
 * @method Generic     inputImage(string $name, string $value = null, array $attributes = [])
 * @method Generic     inputInput(string $name, string $value = null, array $attributes = [])
 * @method Generic     inputMonth(string $name, string $value = null, array $attributes = [])
 * @method Generic     inputNumeric(string $name, string $value = null, array $attributes = [])
 * @method Generic     inputPassword(string $name, string $value = null, array $attributes = [])
 * @method Radio       inputRadio(string $name, string $value = null, array $attributes = [])
 * @method Generic     inputRange(string $name, string $value = null, array $attributes = [])
 * @method Generic     inputSearch(string $name, string $value = null, array $attributes = [])
 * @method Select      inputSelect(string $name, string $value = null, array $attributes = [])
 * @method Generic     inputSubmit(string $name, string $value = null, array $attributes = [])
 * @method Generic     inputTel(string $name, string $value = null, array $attributes = [])
 * @method Generic     inputText(string $name, string $value = null, array $attributes = [])
 * @method Textarea    inputTextarea(string $name, string $value = null, array $attributes = [])
 * @method Generic     inputTime(string $name, string $value = null, array $attributes = [])
 * @method Generic     inputUrl(string $name, string $value = null, array $attributes = [])
 * @method Generic     inputWeek(string $name, string $value = null, array $attributes = [])
 * @method string      label(string $label, array $attributes = [], bool $raw = false)
 * @method string      labelRaw(string $label, array $attributes = [])
 * @method Link        link(string $indent = '    ', string $delimiter = PHP_EOL)
 * @method Meta        meta(string $indent = '    ', string $delimiter = PHP_EOL)
 * @method Ol          ol(string $indent = '    ', string $delimiter = null, array $attributes = [])
 * @method Ol          olRaw(string $indent = '    ', string $delimiter = null, array $attributes = [])
 * @method string      preload(string $href, string $type = 'style', array $attributes = [])
 * @method Script      script(string $indent = '    ', string $delimiter = PHP_EOL)
 * @method Style       style(string $indent = '    ', string $delimiter = PHP_EOL)
 * @method string      tag(string $name, array $attributes = [])
 * @method Title       title(string $indent = '    ', string $delimiter = PHP_EOL)
 * @method Ul          ul(string $indent = '    ', string $delimiter = null, array $attributes = [])
 * @method Ul          ulRaw(string $indent = '    ', string $delimiter = null, array $attributes = [])
 * @method string      voidTag(string $name, array $attributes = [])
 */
class TagFactory
{
    /**
     * @var Doctype
     */
    private Doctype $doctype;

    /**
     * @var array
     */
    protected array $factories = [];

    /**
     * @var array
     */
    protected array $instances = [];

    /**
     * TagFactory constructor.
     *
     * @param EscaperInterface       $escaper
     * @param array                  $services
     * @param ResponseInterface|null $response
     * @param UrlInterface|null      $url
     */
    public function __construct(
        private readonly EscaperInterface $escaper,
        array $services = [],
        private readonly ?ResponseInterface $response = null,
        private readonly ?UrlInterface $url = null,
    ) {
        $this->doctype   = new Doctype();
        $this->factories = $this->getDefaultServices();

        foreach ($services as $name => $definition) {
            $this->set($name, $definition);
        }
    }

    /**
     * Magic call to make the helper objects available as methods.
     *
     * @param string $name
     * @param array  $arguments
     *
     * @return mixed
     * @throws Exception
     */
    public function __call(string $name, array $arguments): mixed
    {
        $helper = $this->newInstance($name);

        return call_user_func_array([$helper, '__invoke'], $arguments);
    }

    /**
     * @param string $name
     *
     * @return bool
     */
    public function has(string $name): bool
    {
        return isset($this->factories[$name]);
    }

    /**
     * Create or return a cached instance of the helper.
     *
     * @param string $name
     *
     * @return mixed
     * @throws Exception
     */
    public function newInstance(string $name): mixed
    {
        if (!isset($this->factories[$name])) {
            throw new Exception('Service ' . $name . ' is not registered');
        }

        if (!isset($this->instances[$name])) {
            $this->instances[$name] = ($this->factories[$name])();
        }

        return $this->instances[$name];
    }

    /**
     * Register a helper via a callable. Passing a new definition clears any
     * cached instance so the next call to newInstance() rebuilds it.
     *
     * @param string   $name
     * @param callable $definition
     */
    public function set(string $name, callable $definition): void
    {
        $this->factories[$name] = $definition;
        unset($this->instances[$name]);
    }

    /**
     * Default service recipes. Every entry is a callable that returns a
     * fully-constructed helper instance. Services are built lazily and cached.
     *
     * @return array
     */
    protected function getDefaultServices(): array
    {
        $escaper  = $this->escaper;
        $doctype  = $this->doctype;
        $response = $this->response;
        $url      = $this->url;

        return [
            'a'                  => fn() => new Anchor($escaper, $doctype),
            'aRaw'               => fn() => new Anchor($escaper, $doctype, true),
            'base'               => fn() => new Base($escaper, $doctype),
            'body'               => fn() => new Body($escaper, $doctype),
            'breadcrumbs'        => fn() => new Breadcrumbs($escaper, $url),
            'button'             => fn() => new Button($escaper, $doctype),
            'buttonRaw'          => fn() => new Button($escaper, $doctype, true),
            'close'              => fn() => new Close($escaper, $doctype),
            'doctype'            => fn() => $doctype,
            'element'            => fn() => new Element($escaper, $doctype),
            'elementRaw'         => fn() => new Element($escaper, $doctype, true),
            'form'               => fn() => new Form($escaper, $doctype),
            'friendlyTitle'      => fn() => new FriendlyTitle($escaper),
            'img'                => fn() => new Img($escaper, $doctype),
            'inputCheckbox'      => fn() => new Checkbox($escaper, $doctype),
            'inputColor'         => fn() => new Generic($escaper, $doctype, 'color'),
            'inputDate'          => fn() => new Generic($escaper, $doctype, 'date'),
            'inputDateTime'      => fn() => new Generic($escaper, $doctype, 'datetime'),
            'inputDateTimeLocal' => fn() => new Generic($escaper, $doctype, 'datetime-local'),
            'inputEmail'         => fn() => new Generic($escaper, $doctype, 'email'),
            'inputFile'          => fn() => new Generic($escaper, $doctype, 'file'),
            'inputHidden'        => fn() => new Generic($escaper, $doctype, 'hidden'),
            'inputImage'         => fn() => new Generic($escaper, $doctype, 'image'),
            'inputInput'         => fn() => new Generic($escaper, $doctype),
            'inputMonth'         => fn() => new Generic($escaper, $doctype, 'month'),
            'inputNumeric'       => fn() => new Generic($escaper, $doctype, 'number'),
            'inputPassword'      => fn() => new Generic($escaper, $doctype, 'password'),
            'inputRadio'         => fn() => new Radio($escaper, $doctype),
            'inputRange'         => fn() => new Generic($escaper, $doctype, 'range'),
            'inputSearch'        => fn() => new Generic($escaper, $doctype, 'search'),
            'inputSelect'        => fn() => new Select($escaper, $doctype),
            'inputSubmit'        => fn() => new Generic($escaper, $doctype, 'submit'),
            'inputTel'           => fn() => new Generic($escaper, $doctype, 'tel'),
            'inputText'          => fn() => new Generic($escaper, $doctype, 'text'),
            'inputTextarea'      => fn() => new Textarea($escaper, $doctype),
            'inputTime'          => fn() => new Generic($escaper, $doctype, 'time'),
            'inputUrl'           => fn() => new Generic($escaper, $doctype, 'url'),
            'inputWeek'          => fn() => new Generic($escaper, $doctype, 'week'),
            'label'              => fn() => new Label($escaper, $doctype),
            'labelRaw'           => fn() => new Label($escaper, $doctype, true),
            'link'               => fn() => new Link($escaper, $doctype),
            'meta'               => fn() => new Meta($escaper, $doctype),
            'ol'                 => fn() => new Ol($escaper, $doctype),
            'olRaw'              => fn() => new Ol($escaper, $doctype, true),
            'preload'            => fn() => new Preload($escaper, $response),
            'script'             => fn() => new Script($escaper, $doctype),
            'style'              => fn() => new Style($escaper, $doctype),
            'tag'                => fn() => new Tag($escaper, $doctype),
            'title'              => fn() => new Title($escaper, $doctype),
            'ul'                 => fn() => new Ul($escaper, $doctype),
            'ulRaw'              => fn() => new Ul($escaper, $doctype, true),
            'voidTag'            => fn() => new VoidTag($escaper, $doctype),
        ];
    }
}
