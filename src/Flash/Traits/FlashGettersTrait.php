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

namespace Phalcon\Flash\Traits;

use Phalcon\Html\Escaper\EscaperInterface;
use Phalcon\Session\ManagerInterface as SessionInterface;

/**
 * Class AbstractFlash
 *
 * @package Phalcon\Flash
 *
 * Shows HTML notifications related to different circumstances. Classes can be
 * stylized using CSS
 *
 *```php
 * $flash->success("The record was successfully deleted");
 * $flash->error("Cannot open the file");
 *```
 * @property bool                  $autoescape
 * @property bool                  $automaticHtml
 * @property array                 $cssClasses
 * @property string                $customTemplate
 * @property EscaperInterface|null $escaperService
 * @property bool                  $implicitFlush
 * @property array                 $messages
 * @property SessionInterface|null $sessionService
 */
trait FlashGettersTrait
{
    /**
     * @var bool
     */
    protected bool $autoescape = true;

    /**
     * @var bool
     */
    protected bool $automaticHtml = true;

    /**
     * @var array
     */
    protected array $cssClasses = [];

    /**
     * @var array
     */
    protected array $cssIconClasses = [];

    /**
     * @var string
     */
    protected string $customTemplate = '';

    /**
     * @var EscaperInterface|null
     */
    protected EscaperInterface | null $escaperService = null;

    /**
     * @var bool
     */
    protected bool $implicitFlush = true;

    /**
     * @var array
     */
    protected array $messages = [];

    /**
     * @var SessionInterface|null
     */
    protected SessionInterface | null $sessionService = null;

    /**
     * Clears accumulated messages when implicit flush is disabled
     */
    public function clear(): void
    {
        $this->messages = [];
    }

    /**
     * Returns the flag that defines whether to automatically escape content or not
     *
     * @return bool
     */
    public function getAutoescape(): bool
    {
        return $this->autoescape;
    }

    /**
     * Returns the array of the CSS classes for formatting messages. The key is
     * the type of message and the value is the CSS class
     *
     * @return array
     */
    public function getCssClasses(): array
    {
        return $this->cssClasses;
    }

    /**
     * Returns the array of the icon CSS classes for formatting messages. The
     * key is the type of message and the value is the icon CSS class
     *
     * @return array
     */
    public function getCssIconClasses(): array
    {
        return $this->cssIconClasses;
    }

    /**
     * Returns the custom template for formatting messages
     *
     * @return string
     */
    public function getCustomTemplate(): string
    {
        return $this->customTemplate;
    }
}
