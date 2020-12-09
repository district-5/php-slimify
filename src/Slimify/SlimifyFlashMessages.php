<?php
namespace Slimify;

use Exception;
use Skinny\Session;

/**
 * Class SlimifyFlashMessages
 * @package Slimify
 */
class SlimifyFlashMessages
{
    /**
     * @var Session
     */
    private $session;

    /**
     * @var array|null
     */
    private $data = null;

    /**
     * SlimifyFlashMessages constructor.
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
     */
    public function addSuccess(string $message)
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
     */
    public function addError(string $message)
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
     */
    public function addInfo(string $message)
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
     */
    public function addWarning(string $message)
    {
        $this->setup();
        $this->data[] = ['type' => 'warning', 'message' => $message];
        $this->save();
        return $this;
    }

    /**
     * @return array
     */
    public function getMessages()
    {
        $this->setup();
        $messages = $this->data;
        $this->data = [];
        $this->save();
        return $messages;
    }

    /**
     * Save the changes to the session.
     */
    private function save()
    {
        try {
            $this->session->set('slimifyFlash', $this->data);
        } catch (Exception $e) {
        }
    }

    /**
     * Setup the local variables.
     */
    private function setup()
    {
        if (null === $this->session) {
            $this->session = Session::getInstance();
        }
        if (null !== $this->data) {
            return;
        }
        try {
            $flash = $this->session->get('slimifyFlash');
        } catch (Exception $e) {
            $flash = [];
        }
        if (!is_array($flash)) {
            $flash = [];
        }

        $this->data = $flash;
    }
}
