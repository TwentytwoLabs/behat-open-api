<?php

declare(strict_types=1);

namespace TwentytwoLabs\BehatOpenApiExtension\Context;

use Behat\Mink\Driver\BrowserKitDriver;
use Behat\MinkExtension\Context\RawMinkContext;
use Symfony\Component\BrowserKit\AbstractBrowser;
use Symfony\Component\HttpFoundation\File\UploadedFile;

abstract class RawRestContext extends RawMinkContext
{
    protected function getClient(): AbstractBrowser
    {
        /** @var BrowserKitDriver $driver */
        $driver = $this->getSession()->getDriver();

        return $driver->getClient();
    }

    /**
     * @return array<string, string|string[]>
     */
    protected function getResponseHeaders(): array
    {
        return $this->getSession()->getResponseHeaders();
    }

    protected function getResponseHeader(string $name): ?string
    {
        return $this->getSession()->getResponseHeader($name);
    }

    protected function getContent(): string
    {
        return $this->getSession()->getDriver()->getContent();
    }

    /**
     * @param array<int|string, mixed> $parameters
     * @param array<int|string, mixed> $files
     */
    protected function send(
        string $method,
        string $url,
        array $parameters = [],
        array $files = [],
        ?string $content = null
    ): void {
        foreach ($files as $originalName => &$file) {
            if (is_string($file)) {
                $file = new UploadedFile($file, $originalName);
            }
        }

        $client = $this->getClient();

        $client->followRedirects(false);
        $client->request($method, $url, $parameters, $files, [], $content);
        $client->followRedirects();

        $client->setServerParameters([]);
    }
}
