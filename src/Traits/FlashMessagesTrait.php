<?php

namespace Slimify\Traits;

use Slimify\SlimifyFlashMessages;

/**
 * Trait FlashMessagesTrait
 * @package Slimify\Traits
 */
trait FlashMessagesTrait
{
    /**
     * @var SlimifyFlashMessages|null
     */
    protected ?SlimifyFlashMessages $flashMessages = null;

    /**
     * @return SlimifyFlashMessages
     * @noinspection PhpUnused
     */
    public function flash(): SlimifyFlashMessages
    {
        if ($this->flashMessages === null) {
            $this->flashMessages = new SlimifyFlashMessages();
        }
        return $this->flashMessages;
    }
}
