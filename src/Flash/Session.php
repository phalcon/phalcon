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

use Phalcon\Session\ManagerInterface;

/**
 * This is an implementation of the Phalcon\Flash\FlashInterface that
 * temporarily stores the messages in session, then messages can be printed in
 * the next request.
 */
class Session extends AbstractFlash
{
    private const SESSION_KEY = '_flashMessages';

    /**
     * Clear messages in the session messenger
     *
     * @throws Exception
     */
    public function clear(): void
    {
        $this->getSessionMessages(true);
        parent::clear();
    }

    /**
     * Returns the messages in the session flasher
     *
     * @param mixed|null $type
     * @param bool       $remove
     *
     * @return array
     * @throws Exception
     */
    public function getMessages($type = null, bool $remove = true): array
    {
        return $this->getSessionMessages($remove, $type);
    }

    /**
     * Checks whether there are messages
     *
     * @param mixed|null $type
     *
     * @return bool
     * @throws Exception
     */
    public function has($type = null): bool
    {
        $messages = $this->getSessionMessages(false);

        if (true !== is_string($type)) {
            return true;
        }

        return isset($messages[$type]);
    }

    /**
     * Adds a message to the session flasher
     *
     * @param string $type
     * @param mixed  $message
     *
     * @return string|null
     * @throws Exception
     */
    public function message(string $type, $message): ?string
    {
        $messages = $this->getSessionMessages(false);

        if (true !== isset($messages[$type])) {
            $messages[$type] = [];
        }

        $messages[$type][] = $message;

        $this->setSessionMessages($messages);

        return null;
    }

    /**
     * Prints the messages in the session flasher
     *
     * @param bool $remove
     *
     * @throws Exception
     */
    public function output(bool $remove = true): void
    {
        $messages = $this->getSessionMessages($remove);

        foreach ($messages as $type => $message) {
            $this->outputMessage($type, $message);
        }

        parent::clear();
    }

    /**
     * Returns the messages stored in session
     *
     * @param bool       $remove
     * @param mixed|null $type
     *
     * @return array
     * @throws Exception
     */
    protected function getSessionMessages(bool $remove, $type = null): array
    {
        $session  = $this->getSessionService();
        $messages = $session->get(self::SESSION_KEY);

        /**
         * Session might be empty
         */
        if (true !== is_array($messages)) {
            $messages = [];
        }

        if (true === is_string($type)) {
            $return = $messages[$type] ?? [];
            if (true === $remove) {
                unset($messages[$type]);
                $session->set(self::SESSION_KEY, $messages);
            }

            return $return;
        }

        if (true === $remove) {
            $session->remove(self::SESSION_KEY);
        }

        return $messages;
    }

    /**
     * Stores the messages in session
     *
     * @param array $messages
     *
     * @return array
     * @throws Exception
     */
    protected function setSessionMessages(array $messages): array
    {
        $session = $this->getSessionService();

        $session->set(self::SESSION_KEY, $messages);

        return $messages;
    }

    /**
     * Returns the Session Service
     *
     * @return ManagerInterface
     * @throws Exception
     */
    public function getSessionService(): ManagerInterface
    {
        if (null !== $this->sessionService) {
            return $this->sessionService;
        }

        if (
            null !== $this->container &&
            true === $this->container->has('session')
        ) {
            $this->sessionService = $this->container->getShared('escaper');

            return $this->sessionService;
        }

        throw new Exception(
            'A dependency injection container is required to access ' .
            'the "session" service'
        );
    }
}
