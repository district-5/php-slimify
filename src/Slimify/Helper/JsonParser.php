<?php

namespace Slimify\Helper;

use Exception;
use Slim\Psr7\Request;
use Slimify\Helper\Exception\InvalidJsonRequestException;

/**
 * Class JsonParser
 * @package Slimify\Helper
 */
class JsonParser
{
    /**
     * @var array
     */
    protected array $json = [];

    /**
     * @param Request $request
     * @throws InvalidJsonRequestException
     */
    public function __construct(Request $request)
    {
        $body = $request->getBody();
        $string = $body->__toString();
        $decoded = @json_decode($string, true);
        if (!is_array($decoded)) {
            throw new InvalidJsonRequestException(
                'Invalid JSON request'
            );
        }

        $this->json = $decoded;
    }

    /**
     * @param string $key
     * @param bool $required
     * @param mixed|array $default
     * @return mixed|null
     * @throws InvalidJsonRequestException
     */
    public function anything(string $key, bool $required = true, mixed $default = null): mixed
    {
        $body = $this->json;
        if (!array_key_exists($key, $body)) {
            if ($required === true) {
                throw new InvalidJsonRequestException(
                    sprintf('Request key was not present "%s"', $key)
                );
            }
            return $default;
        }

        return $body[$key];
    }

    /**
     * Get an integer from the JSON request.
     *
     * @param string $key
     * @param bool $required
     * @param mixed $default
     * @return int|null
     * @throws InvalidJsonRequestException
     */
    public function int(string $key, bool $required = true, mixed $default = null): int|null
    {
        $value = $this->anything($key, $required, $default);
        $type = gettype($value);
        if ($type === 'integer') {
            return $value;
        }
        if ($type === 'string' && is_numeric($value) && strval(intval($value)) == $value) {
            return (int)$value;
        }

        if ($required === true) {
            throw new InvalidJsonRequestException(
                sprintf('Request key was not an integer "%s"', $key)
            );
        }

        return $default;
    }

    /**
     * Get a string from the JSON request.
     *
     * @param string $key
     * @param bool $required
     * @param mixed $default
     * @return string|null
     * @throws InvalidJsonRequestException
     */
    public function string(string $key, bool $required = true, mixed $default = null): string|null
    {
        $value = $this->anything($key, $required, $default);
        $type = gettype($value);
        if ($type === 'string') {
            return $value;
        }

        if ($required === true) {
            throw new InvalidJsonRequestException(
                sprintf('Request key was not a string "%s"', $key)
            );
        }

        return $default;
    }

    /**
     * Get a boolean from the JSON request.
     *
     * @param string $key
     * @param bool $required
     * @param mixed $default
     * @return bool|null
     * @throws InvalidJsonRequestException
     */
    public function bool(string $key, bool $required = true, mixed $default = null): bool|null
    {
        $value = $this->anything($key, $required, $default);
        $type = gettype($value);
        if ($type === 'boolean') {
            return $value;
        }
        if ($type === 'string') {
            if (in_array($value, ['true', 'false'])) {
                return $value === 'true';
            }
            if (in_array($value, ['1', '0'])) {
                return $value === '1';
            }
            if (in_array($value, ['yes', 'no'])) {
                return $value === 'yes';
            }
        }
        if ($type === 'integer' && in_array($value, [0, 1])) {
            return $value === 1;
        }

        if ($required === true) {
            throw new InvalidJsonRequestException(
                sprintf('Request key was not a boolean "%s"', $key)
            );
        }

        return $default;
    }

    /**
     * Get an array from the JSON request.
     *
     * @param string $key
     * @param bool $required
     * @param mixed $default
     * @return array|null
     * @throws InvalidJsonRequestException
     */
    public function array(string $key, bool $required = true, mixed $default = null): array|null
    {
        $value = $this->anything($key, $required, $default);
        $type = gettype($value);
        if ($type === 'array') {
            return $value;
        }

        if ($required === true) {
            throw new InvalidJsonRequestException(
                sprintf('Request key was not an array "%s"', $key)
            );
        }

        return $default;
    }

    /**
     * Get a float from the JSON request.
     *
     * @param string $key
     * @param bool $required
     * @param mixed $default
     * @return float|null
     * @throws InvalidJsonRequestException
     */
    public function float(string $key, bool $required = true, mixed $default = null): float|null
    {
        $value = $this->anything($key, $required, $default);
        $type = gettype($value);
        if ($type === 'double') {
            return $value;
        }
        if ($type === 'string' && is_numeric($value)) {
            return (float)$value;
        }

        if ($required === true) {
            throw new InvalidJsonRequestException(
                sprintf('Request key was not a float "%s"', $key)
            );
        }

        return $default;
    }

    /**
     * Get a MongoID string from the JSON request.
     *
     * @param string $key
     * @param bool $required
     * @param mixed $default
     * @return string|null
     * @throws InvalidJsonRequestException
     * @noinspection PhpFullyQualifiedNameUsageInspection
     */
    public function mongoId(string $key, bool $required = true, mixed $default = null): string|null
    {
        $value = $this->string($key, $required. $default);
        if (preg_match('/^[0-9a-fA-F]{24}$/', $value)) {
            if (class_exists('MongoDB\BSON\ObjectId')) {
                try {
                    $tmp = new \MongoDB\BSON\ObjectId($value);
                    if ($tmp->__toString() === $value) {
                        return $value;
                    }
                    if ($required === true) {
                        throw new InvalidJsonRequestException(
                            sprintf('Request key was not a MongoID "%s"', $key)
                        );
                    }

                    return $default;
                } catch (Exception) {
                }
            }
            return $value;
        }

        if ($required === true) {
            throw new InvalidJsonRequestException(
                sprintf('Request key was not a MongoID "%s"', $key)
            );
        }

        return $default;
    }
}
