<?php

namespace Slimify\Traits;

use Slim\Psr7\Request;
use Slim\Views\PhpRenderer;

/**
 * Trait RequestHeaderValuesTrait
 * @package Slimify\Traits
 */
trait RequestHeaderValuesTrait
{
    /**
     * @var Request|null
     */
    protected ?Request $request = null;

    /**
     * @return string|null
     * @noinspection PhpUnused
     */
    public function getIp(): ?string
    {
        $params = $this->request->getServerParams();
        if (array_key_exists('REMOTE_ADDR', $params)) {
            return $params['REMOTE_ADDR'];
        }
        return null;
    }

    /**
     * @return bool
     * @noinspection PhpUnused
     */
    public function isAjaxRequest(): bool
    {
        return $this->getHeaderLine('X-Requested-With') === 'XMLHttpRequest';
    }

    /**
     * @param string $key
     * @return string
     */
    public function getHeaderLine(string $key): string
    {
        return $this->request->getHeaderLine(
            $key
        );
    }
}
