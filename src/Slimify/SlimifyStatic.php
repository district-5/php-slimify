<?php

namespace Slimify;

/**
 * Class SlimifyStatic
 * @package Slimify
 */
class SlimifyStatic
{
    /**
     * Static variable, holding the instance of this Singleton.
     *
     * @var SlimifyStatic|null
     */
    protected static ?SlimifyStatic $_instance = null;

    /**
     * @var SlimifyInstance
     */
    private SlimifyInstance $app;

    /**
     * @var array
     */
    private array $errorViewParams = [];

    /**
     * Env constructor. Protected to avoid direct construction.
     * @param SlimifyInstance $app
     */
    protected function __construct(SlimifyInstance $app)
    {
        $this->app = $app;
    }

    /**
     * Retrieve an instance of this object.
     *
     * @param SlimifyInstance|null $app
     * @return SlimifyStatic
     */
    public static function retrieve(?SlimifyInstance $app = null): SlimifyStatic
    {
        if (null === self::$_instance) {
            self::$_instance = new self($app);
        }
        return self::$_instance;
    }

    /**
     * @return SlimifyInstance
     * @noinspection PhpUnused
     */
    public function getApp(): SlimifyInstance
    {
        return $this->app;
    }

    /**
     * @param array $params
     * @return $this
     * @noinspection PhpUnused
     */
    public function setErrorViewParams(array $params): SlimifyStatic
    {
        $this->errorViewParams = $params;
        return $this;
    }

    /**
     * @return array
     * @noinspection PhpUnused
     */
    public function getErrorViewParams(): array
    {
        return $this->errorViewParams;
    }
}
