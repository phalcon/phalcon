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

namespace Phalcon\Flash;

use Phalcon\Contracts\Flash\FlashTypes;
use Phalcon\Di\AbstractInjectionAware;
use Phalcon\Flash\Exceptions\EscaperServiceUnavailable;
use Phalcon\Flash\Exceptions\FlashMessageNotStringOrArray;
use Phalcon\Html\Escaper\EscaperInterface;
use Phalcon\Session\ManagerInterface as SessionInterface;
use Phalcon\Traits\Support\Helper\Str\InterpolateTrait;

use function htmlspecialchars;
use function is_array;
use function is_string;

use const ENT_QUOTES;
use const PHP_EOL;

/**
 * Shows HTML notifications related to different circumstances. Classes can be
 * stylized using CSS
 *
 *```php
 * $flash->success("The record was successfully deleted");
 * $flash->error("Cannot open the file");
 *```
 *
 * @phpstan-import-type flash_messages from FlashTypes
 * @phpstan-import-type flash_css_classes from FlashTypes
 */
abstract class AbstractFlash extends AbstractInjectionAware implements FlashInterface
{
    use InterpolateTrait;

    protected bool $autoescape    = true;
    protected bool $automaticHtml = true;
    /**
     * @phpstan-var flash_css_classes
     */
    protected array $cssClasses = [];
    /**
     * @phpstan-var flash_css_classes
     */
    protected array $cssIconClasses                   = [];
    protected string $customTemplate                  = '';
    protected EscaperInterface | null $escaperService = null;
    protected bool $implicitFlush                     = true;
    /**
     * @phpstan-var flash_messages
     */
    protected array $messages                         = [];
    protected SessionInterface | null $sessionService = null;

    /**
     * AbstractFlash constructor.
     */
    public function __construct(
        EscaperInterface | null $escaper = null,
        SessionInterface | null $session = null
    ) {
        $this->escaperService = $escaper;
        $this->sessionService = $session;

        $this->cssClasses = [
            'error'   => 'errorMessage',
            'notice'  => 'noticeMessage',
            'success' => 'successMessage',
            'warning' => 'warningMessage',
        ];
    }

    /**
     * Clears accumulated messages when implicit flush is disabled
     */
    public function clear(): void
    {
        $this->messages = [];
    }

    /**
     * Shows a HTML error message
     *
     *```php
     * $flash->error("This is an error");
     *```
     */
    public function error(string $message): string | null
    {
        return $this->message('error', $message);
    }

    /**
     * Returns the flag that defines whether to automatically escape content or not
     */
    public function getAutoescape(): bool
    {
        return $this->autoescape;
    }

    /**
     * Returns the flag that defines whether to automatically use HTML or not
     */
    public function getAutomaticHtml(): bool
    {
        return $this->automaticHtml;
    }

    /**
     * Returns the array of the CSS classes for formatting messages. The key is
     * the type of message and the value is the CSS class
     *
     * @phpstan-return flash_css_classes
     */
    public function getCssClasses(): array
    {
        return $this->cssClasses;
    }

    /**
     * Returns the array of the icon CSS classes for formatting messages. The
     * key is the type of message and the value is the icon CSS class
     *
     * @phpstan-return flash_css_classes
     */
    public function getCssIconClasses(): array
    {
        return $this->cssIconClasses;
    }

    /**
     * Returns the custom template for formatting messages
     */
    public function getCustomTemplate(): string
    {
        return $this->customTemplate;
    }

    /**
     * Returns the Escaper Service
     *
     * @throws Exception
     */
    public function getEscaperService(): EscaperInterface
    {
        if (null !== $this->escaperService) {
            return $this->escaperService;
        }

        if (
            null !== $this->container &&
            true === $this->container->has("escaper")
        ) {
            /** @var EscaperInterface $escaper */
            $escaper = $this->container->getShared("escaper");

            $this->escaperService = $escaper;

            return $escaper;
        }

        throw new EscaperServiceUnavailable();
    }

    /**
     * Outputs a message. Delivery semantics differ per implementation:
     * `Direct` renders and emits immediately, `Session` stores the raw
     * message for output on a later request.
     */
    abstract public function message(string $type, mixed $message): string | null;

    /**
     * Shows a HTML notice/information message
     *
     *```php
     * $flash->notice("This is an information");
     *```
     */
    public function notice(string $message): string | null
    {
        return $this->message('notice', $message);
    }

    /**
     * Outputs a message formatting it with HTML
     *
     *```php
     * $flash->outputMessage("error", $message);
     *```
     *
     * @throws Exception
     */
    public function outputMessage(string $type, mixed $message): string | null
    {
        $content = "";

        if (!is_array($message) && !is_string($message)) {
            throw new FlashMessageNotStringOrArray();
        }

        /**
         * Make this an array. Same code processes string and array
         */
        if (!is_array($message)) {
            $message = [$message];
        }

        /** @var array<array-key, string> $message */
        foreach ($message as $item) {
            $prepared = $this->prepareEscapedMessage($item);
            $html     = $this->prepareHtmlMessage($type, $prepared);

            if (true === $this->implicitFlush) {
                echo $html;
            } else {
                $content          .= $html;
                $this->messages[] = $html;
            }
        }

        /**
         * If we are here then implicitFlush is off - otherwise it has been
         * echoed back during the loop. Return the string back.
         */
        return $content;
    }

    /**
     * Set the autoescape mode in generated HTML
     */
    public function setAutoescape(bool $autoescape): static
    {
        $this->autoescape = $autoescape;

        return $this;
    }

    /**
     * Set if the output must be implicitly formatted with HTML
     */
    public function setAutomaticHtml(bool $automaticHtml): static
    {
        $this->automaticHtml = $automaticHtml;

        return $this;
    }

    /**
     * Set an array with CSS classes to format the messages
     *
     * @phpstan-param flash_css_classes $cssClasses
     */
    public function setCssClasses(array $cssClasses): static
    {
        $this->cssClasses = $cssClasses;

        return $this;
    }

    /**
     * Set an array with CSS classes to format the icon messages
     *
     * @phpstan-param flash_css_classes $cssIconClasses
     */
    public function setCssIconClasses(array $cssIconClasses): static
    {
        $this->cssIconClasses = $cssIconClasses;

        return $this;
    }

    /**
     * Set a custom template for showing the messages
     */
    public function setCustomTemplate(string $customTemplate): static
    {
        $this->customTemplate = $customTemplate;

        return $this;
    }

    /**
     * Sets the Escaper Service
     */
    public function setEscaperService(EscaperInterface $escaperService): static
    {
        $this->escaperService = $escaperService;

        return $this;
    }

    /**
     * Set whether the output must be implicitly flushed to the output or
     * returned as string
     *
     * Note: `output()` is an echo API and requires implicit flush to remain
     * enabled (the default). With implicit flush disabled, `message()` returns
     * the rendered string while `output()` does not emit it.
     */
    public function setImplicitFlush(bool $implicitFlush): static
    {
        $this->implicitFlush = $implicitFlush;

        return $this;
    }

    /**
     * Shows a HTML success message
     *
     *```php
     * $flash->success("The process was finished successfully");
     *```
     */
    public function success(string $message): string | null
    {
        return $this->message('success', $message);
    }

    /**
     * Shows a HTML warning message
     *
     *```php
     * $flash->warning("Hey, this is important");
     *```
     */
    public function warning(string $message): string | null
    {
        return $this->message('warning', $message);
    }

    /**
     * Checks the collection and returns the content as a string
     * (array is joined)
     *
     * @phpstan-param flash_css_classes $collection
     */
    private function checkClasses(array $collection, string $type): string
    {
        $content = $collection[$type] ?? '';

        if (!is_array($content)) {
            $content = [$content];
        }

        return implode(' ', $content);
    }

    /**
     * Returns the template for the CSS classes (with icon classes). It will
     * either be the custom one (defined) or the default
     */
    private function getTemplate(string $cssClasses, string $cssIconClasses): string
    {
        $template   = "<div%divString%>%iconString%%message%</div>" . PHP_EOL;
        $divString  = "";
        $iconString = "";

        if (!empty($this->customTemplate)) {
            return $this->customTemplate;
        }

        if (!empty($cssClasses)) {
            $divString = " class=\"%cssClass%\"";
            if (!empty($cssIconClasses)) {
                $iconString = "<i class=\"%cssIconClass%\"></i> ";
            }
        }

        return $this->toInterpolate(
            $template,
            [
                'divString'  => $divString,
                'iconString' => $iconString,
            ]
        );
    }

    /**
     * Returns the message escaped if the autoEscape is true, otherwise the
     * original message is returned
     */
    private function prepareEscapedMessage(string $message): string
    {
        if (true !== $this->autoescape) {
            return $message;
        }

        $escaper = $this->getEscaperService();

        return $escaper->html($message);
    }

    /**
     * Prepares the HTML output for the message. If automaticHtml is not set
     * then the original message is returned
     */
    private function prepareHtmlMessage(string $type, string $message): string
    {
        if (true !== $this->automaticHtml) {
            return $message;
        }

        /**
         * The class lands in a `class="…"` attribute. Escape it so a crafted
         * class cannot break out of the attribute.
         */
        $cssClasses     = htmlspecialchars($this->checkClasses($this->cssClasses, $type), ENT_QUOTES, 'utf-8');
        $cssIconClasses = htmlspecialchars($this->checkClasses($this->cssIconClasses, $type), ENT_QUOTES, 'utf-8');

        return $this->toInterpolate(
            $this->getTemplate($cssClasses, $cssIconClasses),
            [
                "cssClass"     => $cssClasses,
                "cssIconClass" => $cssIconClasses,
                "message"      => $message,
            ]
        );
    }
}
