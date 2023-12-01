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

namespace Phalcon\Mvc;

use Phalcon\Di\Injectable;
use Phalcon\Events\EventsAwareInterface;
use Phalcon\Events\Traits\EventsAwareTrait;
use Phalcon\Mvc\View\Exception;
use Phalcon\Traits\Helper\Str\DirSeparatorTrait;

/**
 * Phalcon\Mvc\View is a class for working with the "view" portion of the
 * model-view-controller pattern. That is, it exists to help keep the view
 * script separate from the model and controller scripts. It provides a system
 * of helpers, output filters, and variable escaping.
 *
 * ```php
 * use Phalcon\Mvc\View;
 *
 * $view = new View();
 *
 * // Setting views directory
 * $view->setViewsDir("app/views/");
 *
 * $view->start();
 *
 * // Shows recent posts view (app/views/posts/recent.phtml)
 * $view->render("posts", "recent");
 * $view->finish();
 *
 * // Printing views output
 * echo $view->getContent();
 * ```
 */
class View extends Injectable implements EventsAwareInterface //ViewInterface
{
    use DirSeparatorTrait;
    use EventsAwareTrait;

    /**
     * Render Level: To the action view
     */
    public const LEVEL_ACTION_VIEW = 1;
    /**
     * Render Level: Render to the templates "after"
     */
    public const LEVEL_AFTER_TEMPLATE = 4;
    /**
     * Render Level: To the templates "before"
     */
    public const LEVEL_BEFORE_TEMPLATE = 2;
    /**
     * Render Level: To the controller layout
     */
    public const LEVEL_LAYOUT = 3;
    /**
     * Render Level: To the main layout
     */
    public const LEVEL_MAIN_LAYOUT = 5;
    /**
     * Render Level: No render any view
     */
    public const LEVEL_NO_RENDER = 0;
    /**
     * @var string|null
     */
    protected ?string $content = '';

    /**
     * @var array
     */
    protected array $viewsDirs = [];

    /**
     * Phalcon\Mvc\View constructor
     */
    public function __construct(
        protected array $options = []
    ) {
    }

    /**
     * Finishes the render process by stopping the output buffering
     *
     * @return $this
     */
    public function finish(): View
    {
        ob_end_clean();

        return $this;
    }

    /**
     * Sets the views directory. Depending on your platform,
     * always add a trailing slash or backslash
     *
     * @param array|string $viewsDir
     *
     * @return $this
     * @throws Exception
     */
    public function setViewsDir(array | string $viewsDir): View
    {
        if (is_string($viewsDir)) {
            $this->viewsDirs = [$this->toDirSeparator($viewsDir)];
        } else {
            $newViewsDir = [];

            foreach ($viewsDir as $position => $directory) {
                if (!is_string($directory)) {
                    throw new Exception(
                        "Views directory item must be a string"
                    );
                }

                $newViewsDir[$position] = $this->toDirSeparator($directory);
            }

            $this->viewsDirs = $newViewsDir;
        }

        return $this;
    }

    /**
     * Starts rendering process enabling the output buffering
     *
     * @return $this
     */
    public function start(): View
    {
        ob_start();

        $this->content = null;

        return $this;
    }
}
