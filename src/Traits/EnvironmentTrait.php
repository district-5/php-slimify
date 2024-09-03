<?php

namespace Slimify\Traits;

/**
 * Trait EnvironmentTrait
 * @package Slimify\Traits
 */
trait EnvironmentTrait
{
    /**
     * @var bool|null
     */
    protected ?bool $development = null;

    /**
     * @return bool
     */
    public function isDevelopment(): bool
    {
        return $this->development;
    }

    /**
     * @return bool
     * @noinspection PhpUnused
     */
    public function isProduction(): bool
    {
        return $this->isDevelopment() === false;
    }

    /**
     * @param bool $isDevelopment
     * @return $this
     */
    public function setDevelopment(bool $isDevelopment): static
    {
        $this->development = $isDevelopment;
        return $this;
    }
}
