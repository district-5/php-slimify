<?php /** @noinspection PhpUnused */

namespace Slimify;

/**
 * Class SlimifyCors
 * @package Slimify
 */
class SlimifyCors
{
    /**
     * Static variable, holding the instance of this Singleton.
     *
     * @var SlimifyCors|null
     */
    protected static ?SlimifyCors $_instance = null;

    /**
     * @var array
     */
    private array $allowedOrigins = [];

    /**
     * @var array
     */
    private array $allowedMethods = [];

    /**
     * @var array
     */
    private array $allowedHeaders = [];

    /**
     * @var array
     */
    private array $exposedHeaders = [];

    /**
     * @var bool
     */
    private bool $allowCredentials = false;

    /**
     * Default 5 seconds, but not required to be provided.
     *
     * @var int
     * @see https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Access-Control-Max-Age
     */
    private int $maxAge = 0;

    /**
     * @var bool
     */
    private bool $hasData = false;

    /**
     * Retrieve an instance of this object.
     *
     * @return SlimifyCors
     */
    public static function retrieve(): SlimifyCors
    {
        if (null === self::$_instance) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }

    /**
     * Set the allowed origins.
     * @param array $allowedOrigins
     * @return SlimifyCors
     */
    public function setAllowedOrigins(array $allowedOrigins): self
    {
        $this->allowedOrigins = $allowedOrigins;
        $this->hasData = true;
        return $this;
    }

    /**
     * Set the allowed methods.
     * @param array $allowedMethods
     * @return SlimifyCors
     */
    public function setAllowedMethods(array $allowedMethods): self
    {
        $this->allowedMethods = $allowedMethods;
        $this->hasData = true;
        return $this;
    }

    /**
     * Set the allowed headers.
     * @param array $allowedHeaders
     * @return SlimifyCors
     */
    public function setAllowedHeaders(array $allowedHeaders): self
    {
        $this->allowedHeaders = $allowedHeaders;
        $this->hasData = true;
        return $this;
    }

    /**
     * Set the exposed headers.
     * @param array $exposedHeaders
     * @return SlimifyCors
     */
    public function setExposedHeaders(array $exposedHeaders): self
    {
        $this->exposedHeaders = $exposedHeaders;
        $this->hasData = true;
        return $this;
    }

    /**
     * Set the 'allow' credentials.
     * @param bool $allowCredentials
     * @return SlimifyCors
     */
    public function setAllowCredentials(bool $allowCredentials): self
    {
        $this->allowCredentials = $allowCredentials;
        $this->hasData = true;
        return $this;
    }

    /**
     * Set the max age.
     * @param int $maxAge
     * @return SlimifyCors
     */
    public function setMaxAge(int $maxAge): self
    {
        $this->maxAge = $maxAge;
        $this->hasData = true;
        return $this;
    }

    /**
     * @return array
     */
    public function getAllowedOrigins(): array
    {
        return $this->allowedOrigins;
    }

    /**
     * @return array
     */
    public function getAllowedMethods(): array
    {
        return $this->allowedMethods;
    }

    /**
     * @return array
     */
    public function getAllowedHeaders(): array
    {
        return $this->allowedHeaders;
    }

    /**
     * @return array
     */
    public function getExposedHeaders(): array
    {
        return $this->exposedHeaders;
    }

    /**
     * @return bool
     */
    public function getAllowCredentials(): bool
    {
        return $this->allowCredentials;
    }

    /**
     * @return int
     */
    public function getMaxAge(): int
    {
        return $this->maxAge;
    }

    /**
     * @param string|null $origin
     * @return array
     */
    public function getResponseHeaders(?string $origin = null): array
    {
        $all = [];
        if (!empty($this->getAllowedOrigins())) {
            $all['Access-Control-Allow-Origin'] = implode(', ', $this->allowedOrigins);
        } else {
            $all['Access-Control-Allow-Origin'] = '*';
        }

        if (!empty($origin) && in_array($origin, $this->getAllowedOrigins(), true)) {
            $all['Access-Control-Allow-Origin'] = $origin;
        }

        if (!empty($this->getAllowedMethods())) {
            $all['Access-Control-Allow-Methods'] = implode(', ', $this->allowedMethods);
        }

        if (!empty($this->getAllowedHeaders())) {
            $all['Access-Control-Allow-Headers'] = implode(', ', $this->allowedHeaders);
        }

        if (!empty($this->getExposedHeaders())) {
            $all['Access-Control-Expose-Headers'] = implode(', ', $this->exposedHeaders);
        }

        if ($this->getAllowCredentials()) {
            $all['Access-Control-Allow-Credentials'] = 'true';
        }

        if ($this->getMaxAge() > 0) {
            $all['Access-Control-Max-Age'] = $this->getMaxAge();
        }

        return $all;
    }

    /**
     * @return bool
     */
    public function hasBeenConfigured(): bool
    {
        return $this->hasData;
    }

    /**
     * @return void
     */
    public function reset(): void
    {
        $this->allowedOrigins = [];
        $this->allowedMethods = [];
        $this->allowedHeaders = [];
        $this->exposedHeaders = [];
        $this->allowCredentials = false;
        $this->maxAge = 0;
        $this->hasData = false;
    }
}
