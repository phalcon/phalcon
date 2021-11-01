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
 * @property array $resolved
 * @property array $services
 *
 * @method a(string $href, string $text, array $attributes = [], bool $raw = false): string
 * @method base(string $href, array $attributes = []): string
 * @method body(array $attributes = []): string
 * @method button(string $text, array $attributes = [], bool $raw = false): string
 * @method close(string $tag, bool $raw = false): string
 * @method element(string $tag, string $text, array $attributes = [], bool $raw = false): string
 * @method form(array $attributes = []): string
 * @method img(string $src, array $attributes = []): string
 * @method inputCheckbox(string $name, string $value = null, array $attributes = []): string
 * @method inputColor(string $name, string $value = null, array $attributes = []): string
 * @method inputDate(string $name, string $value = null, array $attributes = []): string
 * @method inputDateTime(string $name, string $value = null, array $attributes = []): string
 * @method inputDateTimeLocal(string $name, string $value = null, array $attributes = []): string
 * @method inputEmail(string $name, string $value = null, array $attributes = []): string
 * @method inputFile(string $name, string $value = null, array $attributes = []): string
 * @method inputHidden(string $name, string $value = null, array $attributes = []): string
 * @method inputImage(string $name, string $value = null, array $attributes = []): string
 * @method inputInput(string $name, string $value = null, array $attributes = []): string
 * @method inputMonth(string $name, string $value = null, array $attributes = []): string
 * @method inputNumeric(string $name, string $value = null, array $attributes = []): string
 * @method inputPassword(string $name, string $value = null, array $attributes = []): string
 * @method inputRadio(string $name, string $value = null, array $attributes = []): string
 * @method inputRange(string $name, string $value = null, array $attributes = []): string
 * @method inputSearch(string $name, string $value = null, array $attributes = []): string
 * @method inputSelect(string $name, string $value = null, array $attributes = []): string
 * @method inputSubmit(string $name, string $value = null, array $attributes = []): string
 * @method inputTel(string $name, string $value = null, array $attributes = []): string
 * @method inputText(string $name, string $value = null, array $attributes = []): string
 * @method inputTextarea(string $name, string $value = null, array $attributes = []): string
 * @method inputTime(string $name, string $value = null, array $attributes = []): string
 * @method inputUrl(string $name, string $value = null, array $attributes = []): string
 * @method inputWeek(string $name, string $value = null, array $attributes = []): string
 * @method label(array $attributes = []): string
 * @method link(string $indent = '    ', string $delimiter = PHP_EOL): string
 * @method meta(string $indent = '    ', string $delimiter = PHP_EOL): string
 * @method ol(string $text, array $attributes = [], bool $raw = false): string
 * @method script(string $indent = '    ', string $delimiter = PHP_EOL): string
 * @method style(string $indent = '    ', string $delimiter = PHP_EOL): string
 * @method title(string $separator = '', string $indent = '', string $delimiter = PHP_EOL): string
 * @method ul(string $text, array $attributes = [], bool $raw = false): string
 */
class TagLocator
{
    /**
     * @var array
     */
    protected array $resolved = [];

    /**
     * @var array
     */
    protected array $services = [];

    /**
     * @param array $services
     */
    public function __construct(array $services = array())
    {
        $this->services = $services;
    }

    /**
     *
     * Magic call to make the helper objects available as methods.
     *
     * @param string $name
     * @param array  $args
     *
     * @return false|mixed
     */
    public function __call(string $name, array $args)
    {
        return call_user_func_array($this->get($name), $args);
    }

    /**
     * @param string   $name
     * @param callable $callable
     */
    public function set(string $name, $callable): TagLocator
    {
        $this->services[$name] = $callable;
        unset($this->resolved[$name]);

        return $this;
    }

    /**
     * @param string $name
     *
     * @return bool
     */
    public function has(string $name): bool
    {
        return isset($this->services[$name]);
    }

    /**
     * @param string $name
     *
     * @return mixed
     * @throws Exception
     */
    public function get(string $name)
    {
        if (true !== $this->has($name)) {
            throw new Exception(
                "Service '" . $name . "' has not been found in the Tag locator"
            );
        }

        if (true !== isset($this->resolved[$name])) {
            $definition = $this->services[$name];
            $this->resolved[$definition] = $definition();
        }

        return $this->resolved[$name];
    }
}
