<?php

declare(strict_types=1);

namespace TwentytwoLabs\Behat\OpenApi\Client;

use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Request;
use Symfony\Component\HttpFoundation\File\UploadedFile;

/**
 * Class GuzzleClient.
 */
class GuzzleClient
{
    private Client $client;
    private array $requestHeaders = [];

    public function __construct()
    {
        $this->client = new Client();
    }

    public function setRequestHeader($name, $value)
    {
        $this->requestHeaders[$name] = $value;
    }

    public function send($method, $url, $parameters = [], $files = [], $content = null, $headers = [])
    {
        $request = new Request($method, $url, array_merge($headers, $this->requestHeaders), $content);

        $response = $this->client->send($request);

        $this->resetRequestHeaders();

        return $response;
    }

    public function getResponse()
    {
        return $this->client->getInternalResponse();
    }

    public function getResponseHeaders(): array
    {
        $response = $this->client->getInternalResponse();

        return array_change_key_case($response->getHeaders(), CASE_LOWER);
    }

    public function getResponseHeader($name): string
    {
        $values = $this->getResponseRawHeader($name);

        return implode(', ', $values);
    }

    public function getResponseRawHeader($name)
    {
        $name = strtolower($name);
        $headers = $this->getResponseHeaders();

        if (isset($headers[$name])) {
            $value = $headers[$name];
            if (!is_array($headers[$name])) {
                $value = [$headers[$name]];
            }
        } else {
            throw new \OutOfBoundsException(
                "The header '$name' doesn't exist"
            );
        }
        return $value;
    }

    public function getResponseContent(): string
    {
        return $this->getResponse()->getContent();
    }

    protected function resetRequestHeaders()
    {
        $this->requestHeaders = [];
    }
}
