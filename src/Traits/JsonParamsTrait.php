<?php

namespace Slimify\Traits;

use Slimify\Helper\Exception\InvalidJsonRequestException;
use Slimify\Helper\JsonParser;
use stdClass;

/**
 * Trait JsonParamsTrait
 * @package Slimify\Traits
 */
trait JsonParamsTrait
{
    /**
     * @var array|null
     */
    protected array|null $jsonBodyArray = null;

    /**
     * @var stdClass|null
     */
    protected stdClass|null $jsonBodyObject = null;

    /**
     * @var JsonParser
     */
    protected JsonParser $jsonParser;

    /**
     * Get an instance of JSON Parser
     *
     * @return JsonParser
     * @throws InvalidJsonRequestException
     */
    public function getJsonParser(): JsonParser
    {
        if (!isset($this->jsonParser)) {
            $this->jsonParser = new JsonParser($this->request);
        }
        return $this->jsonParser;
    }

    /**
     * Get the JSON decoded body from a request.
     * Defaults to an array, but can be formatted as an object.
     *
     * @param bool $asArray
     * @return array|stdClass|null
     * @noinspection PhpUnused
     */
    public function getJsonBody(bool $asArray = true): array|stdClass|null
    {
        if ($asArray) {
            return $this->getJsonBodyArray();
        }

        return $this->getJsonBodyAsObject();
    }

    /**
     * Get the JSON decoded body from a request as an array.
     *
     * @return array|null
     * @noinspection PhpUnused
     */
    public function getJsonBodyArray(): array|null
    {
        if ($this->jsonBodyArray !== null) {
            return $this->jsonBodyArray;
        }
        $body = $this->request->getBody();
        $string = $body->__toString();
        $decoded = @json_decode($string, true);
        if (in_array($decoded, [null, false])) {
            $this->jsonBodyArray = null;
            return null;
        }

        $this->jsonBodyArray = $decoded;
        return $this->jsonBodyArray;
    }

    /**
     * Get the JSON decoded body from a request.
     * Defaults to an array, but can be formatted as an object.
     *
     * @return stdClass|null
     * @noinspection PhpUnused
     */
    public function getJsonBodyAsObject(): stdClass|null
    {
        if ($this->jsonBodyObject !== null) {
            return $this->jsonBodyObject;
        }
        $body = $this->request->getBody();
        $string = $body->__toString();
        $decoded = @json_decode($string, false);
        if (in_array($decoded, [null, false])) {
            $this->jsonBodyObject = null;
            return null;
        }

        $this->jsonBodyObject = $decoded;
        return $this->jsonBodyObject;
    }
}
