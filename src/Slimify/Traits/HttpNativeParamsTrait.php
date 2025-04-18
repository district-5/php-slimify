<?php

namespace Slimify\Traits;

use Slim\Psr7\Request;

/**
 * Trait HttpNativeParamsTrait
 * @package Slimify\Traits
 */
trait HttpNativeParamsTrait
{
    /**
     * @var Request|null
     */
    protected ?Request $request = null;

    /**
     * @var array|null
     */
    protected ?array $bodyParams = null;

    /**
     * @var array|null
     */
    protected ?array $queryParams = null;

    /**
     * @return array|null
     * @noinspection PhpUnused
     */
    public function getAllPostParams(): ?array
    {
        if ($this->bodyParams === null) {
            $this->bodyParams = $this->request->getParsedBody();
        }
        return $this->bodyParams;
    }

    /**
     * Get a post parameter (POST).
     *
     * @param string $key
     * @param mixed|null $default
     * @return mixed|null
     */
    public function getPostParam(string $key, mixed $default = null): mixed
    {
        if ($this->bodyParams === null) {
            $this->bodyParams = $this->request->getParsedBody();
        }

        if (is_array($this->bodyParams) && array_key_exists($key, $this->bodyParams)) {
            return $this->bodyParams[$key];
        }
        return $default;
    }

    /**
     * Get an integer post parameter (POST).
     *
     * @param string $key
     * @param int|null $default
     * @return int|null
     * @noinspection PhpUnused
     */
    public function getPostParamInt(string $key, int|null $default = null): ?int
    {
        return $this->paramToInt(
            $this->getPostParam($key, $default),
            $default
        );
    }

    /**
     * Get a query parameter (GET).
     *
     * @param string $key
     * @param mixed|null $default
     * @return mixed|null
     */
    public function getQueryParam(string $key, mixed $default = null): mixed
    {
        if ($this->queryParams === null) {
            $this->queryParams = $this->request->getQueryParams();
        }

        if (is_array($this->queryParams) && array_key_exists($key, $this->queryParams)) {
            return $this->queryParams[$key];
        }
        return $default;
    }

    /**
     * Get an integer query parameter (GET).
     * @param string $key
     * @param int|null $default
     * @return int|null
     */
    public function getQueryParamInt(string $key, int|null $default = null): ?int
    {
        return $this->paramToInt(
            $this->getQueryParam($key, $default),
            $default
        );
    }

    /**
     * Get a page number from a request.
     *
     * @param string $key
     * @param int $default
     * @return int|null
     * @noinspection PhpUnused
     */
    public function getPageNumber(string $key = 'page', int $default = 1): ?int
    {
        return $this->getQueryParamInt($key, $default);
    }

    /**
     * @param string|int|mixed $val
     * @param int|null $default
     * @return int|null
     */
    protected function paramToInt(mixed $val, int|null $default = null): ?int
    {
        if (is_int($val)) {
            return $val;
        }
        if (is_numeric($val) && !str_contains($val, '.')) {
            if (strlen($val) > 1 && str_starts_with($val, '0')) {
                return $default;
            }
            return intval($val);
        }
        return $default;
    }
}
