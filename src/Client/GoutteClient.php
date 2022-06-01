<?php

declare(strict_types=1);

namespace TwentytwoLabs\Behat\OpenApi\Client;

use Behat\Mink\Mink;
use Symfony\Component\HttpFoundation\File\UploadedFile;

/**
 * Class GoutteClient.
 */
class GoutteClient
{
    private $client;
    private array $requestHeaders = [];

    public function __construct(Mink $mink)
    {
        $this->client = $mink->getSession()->getDriver()->getClient();
    }

    public function setRequestHeader($name, $value)
    {
        /* taken from Behat\Mink\Driver\BrowserKitDriver::setRequestHeader */
        $contentHeaders = array('CONTENT_LENGTH' => true, 'CONTENT_MD5' => true, 'CONTENT_TYPE' => true);
        $name = str_replace('-', '_', strtoupper($name));

        // CONTENT_* are not prefixed with HTTP_ in PHP when building $_SERVER
        if (!isset($contentHeaders[$name])) {
            $name = 'HTTP_' . $name;
        }
        /* taken from Behat\Mink\Driver\BrowserKitDriver::setRequestHeader */

        $this->requestHeaders[$name] = $value;
    }

    public function send($method, $url, $parameters = [], $files = [], $content = null, $headers = [])
    {
        foreach ($files as $originalName => &$file) {
            if (is_string($file)) {
                $file = new UploadedFile($file, $originalName);
            }
        }

        $this->client->followRedirects(false);
        $this->client->request($method, $url, $parameters, $files, array_merge($headers, $this->requestHeaders), $content);
        $this->client->followRedirects(true);
        $this->resetRequestHeaders();

        $response = $this->client->getInternalResponse();

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
