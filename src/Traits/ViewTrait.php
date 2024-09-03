<?php

namespace Slimify\Traits;

use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Slim\Views\PhpRenderer;
use Slimify\SlimifyInstance;

/**
 * Trait ViewTrait
 * @package Slimify\Traits
 */
trait ViewTrait
{
    /**
     * @var PhpRenderer[]
     */
    protected array $views = [];

    /**
     * Set the view to use, optionally providing 'key' to handle multiple instances of views.
     *
     * @param string $templatePath
     * @param string|null $layout
     * @param array $params
     * @param string $key
     * @return $this
     */
    public function addView(string $templatePath, ?string $layout, array $params = [], string $key = 'default'): static
    {
        $view = new PhpRenderer($templatePath);
        if ($layout !== null) {
            $view->setLayout($layout);
        }
        $view->addAttribute('app', $this);
        foreach ($params as $k => $v) {
            $view->addAttribute($k, $v);
        }
        $this->views[$key] = $view;
        return $this;
    }

    /**
     * @param string $key
     * @return PhpRenderer|null
     * @noinspection PhpUnused
     */
    public function getView(string $key = 'default'): ?PhpRenderer
    {
        if (array_key_exists($key, $this->views)) {
            return $this->views[$key];
        }

        return null;
    }
}
