<?php

declare(strict_types=1);

namespace TwentytwoLabs\BehatOpenApiExtension\Handler;

use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Request;
use Psr\Http\Message\ResponseInterface;

/**
 * Class GuzzleClient.
 */
class GuzzleHandler implements HandlerInterface
{
    private Client $client;
    private array $requestHeaders = [];
    private ?ResponseInterface $response = null;

    public function __construct(string $baseUri)
    {
        $this->client = new Client(['base_uri' => $baseUri, 'http_errors' => false]);
    }

    public function setRequestHeader(string $name, string $value): self
    {
        $this->requestHeaders[$name] = $value;

        return $this;
    }

    public function send(string $method, string $url, array $headers = [], ?string $content = null)
    {
        $request = new Request($method, $url, array_merge($this->requestHeaders, $headers), $content);

        $this->requestHeaders = [];

        $this->response = $this->client->send($request);
    }

    public function getResponseHeaders(): array
    {
        return array_change_key_case($this->response->getHeaders(), CASE_LOWER);
    }

    public function getResponseHeader($name): string
    {
        return $this->response->getHeaderLine($name);
    }

    public function getStatusCode(): int
    {
        return $this->response->getStatusCode();
    }

    public function getResponseContent(): string
    {
        return (string) $this->response->getBody();
    }
}
