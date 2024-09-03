<?php

namespace Slimify;

use District5\SimpleSessionStore\Session;
use District5\SimpleSessionStore\SessionException;
use Exception;

/**
 * Class SlimifyFlashMessages
 * @package Slimify
 */
class SlimifyFlashMessages
{
    /**
     * @var Session
     */
    private Session $session;

    /**
     * @var array
     */
    private array $data = [];

    /**
     * SlimifyFlashMessages constructor.
     * @throws SessionException
     */
    public function __construct()
    {
        $this->setup();
    }

    /**
     * Add a success message
     *
     * @param string $message
     * @return SlimifyFlashMessages
     * @noinspection PhpUnused
     * @throws SessionException
     */
    public function addSuccess(string $message): SlimifyFlashMessages
    {
        $this->setup();
        $this->data[] = ['type' => 'success', 'message' => $message];
        $this->save();
        return $this;
    }

    /**
     * Add an error message
     *
     * @param string $message
     * @return SlimifyFlashMessages
     * @noinspection PhpUnused
     * @throws SessionException
     */
    public function addError(string $message): SlimifyFlashMessages
    {
        $this->setup();
        $this->data[] = ['type' => 'error', 'message' => $message];
        $this->save();
        return $this;
    }

    /**
     * Add an info message
     *
     * @param string $message
     * @return SlimifyFlashMessages
     * @noinspection PhpUnused
     * @throws SessionException
     */
    public function addInfo(string $message): SlimifyFlashMessages
    {
        $this->setup();
        $this->data[] = ['type' => 'info', 'message' => $message];
        $this->save();
        return $this;
    }

    /**
     * Add a warning message
     *
     * @param string $message
     * @return SlimifyFlashMessages
     * @noinspection PhpUnused
     * @throws SessionException
     */
    public function addWarning(string $message): SlimifyFlashMessages
    {
        $this->setup();
        $this->data[] = ['type' => 'warning', 'message' => $message];
        $this->save();
        return $this;
    }

    /**
     * @return array
     * @noinspection PhpUnused
     * @throws SessionException
     */
    public function getMessages(): array
    {
        $this->setup();
        if (empty($this->data)) {
            return [];
        }
        $messages = $this->data;
        $this->data = [];
        $this->save();
        return $messages;
    }

    /**
     * Save the changes to the session.
     */
    private function save(): void
    {
        try {
            $this->session->set('slimifyFlash', $this->data);
        } catch (Exception) {
        }
    }

    /**
     * Set up the local variables.
     * @return void
     * @throws SessionException
     */
    private function setup(): void
    {
        if (!isset($this->session)) {
            $this->session = Session::getInstance();
        }

        try {
            $flash = $this->session->get('slimifyFlash');
        } catch (Exception) {
            $flash = [];
        }
        if (!is_array($flash)) {
            $flash = [];
        }

        $this->data = $flash;
    }
}
