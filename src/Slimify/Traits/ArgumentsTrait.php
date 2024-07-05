<?php /** @noinspection PhpUnused */

namespace Slimify\Traits;

/**
 * Trait ArgumentsTrait
 * @package Slimify\Traits
 */
trait ArgumentsTrait
{
    /**
     * @var array
     */
    protected array $args = [];

    /**
     * @param array $args
     * @return $this
     * @noinspection PhpUnused
     */
    public function setArguments(array $args): static
    {
        $this->args = $args;
        return $this;
    }

    /**
     * @return array
     * @noinspection PhpUnused
     */
    public function getArguments(): array
    {
        return $this->args;
    }

    /**
     * @param string $key
     * @param mixed|null $default (optional) default null
     * @return mixed|null
     * @noinspection PhpUnused
     */
    public function getArgument(string $key, mixed $default = null): mixed
    {
        if (!array_key_exists($key, $this->args)) {
            return null;
        }
        if (strlen($this->args[$key]) > 0) {
            return $this->args[$key];
        }
        return $default;
    }
}
