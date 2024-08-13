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
     * @return string|null
     * @noinspection PhpUnused
     */
    public function getArgument(string $key, mixed $default = null): string|null
    {
        if (!array_key_exists($key, $this->args)) {
            return null;
        }
        if (strlen($this->args[$key]) > 0) {
            return strval($this->args[$key]);
        }
        return $default;
    }

    /**
     * @param string $key
     * @param mixed|null $default
     * @return string|null
     */
    public function getArgumentMongoId(string $key, string|null $default = null): string|null
    {
        $arg = $this->getArgument($key, $default);
        if (!is_string($arg)) {
            return $default;
        }
        if (preg_match('/^[0-9a-fA-F]{24}$/', $arg)) {
            return $arg;
        }
        return $default;
    }
}
