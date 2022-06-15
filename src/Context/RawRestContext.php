<?php

declare(strict_types=1);

namespace TwentytwoLabs\BehatOpenApiExtension\Context;

use Behat\MinkExtension\Context\RawMinkContext;
use Behat\Mink\Driver\Goutte\Client as GoutteClient;
use Symfony\Component\BrowserKit\AbstractBrowser;
use Symfony\Component\HttpFoundation\File\UploadedFile;

/**
 * class RawRestContext.
 */
abstract class RawRestContext extends RawMinkContext
{
    protected ?\DateInterval $time = null;

    protected function getClient(): AbstractBrowser
    {
        return $this->getSession()->getDriver()->getClient();
    }

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

    protected function send(string $method, string $url, array $parameters = [], array $files = [], ?string $content = null)
    {
        foreach ($files as $originalName => &$file) {
            if (is_string($file)) {
                $file = new UploadedFile($file, $originalName);
            }
        }

        $client = $this->getClient();

        $client->followRedirects(false);
        $time = new \DateTime();
        $client->request($method, $url, $parameters, $files, [], $content);
        $this->time = (new \DateTime())->diff($time);
        $client->followRedirects();

        $client->setServerParameters([]);
        if ($client instanceof GoutteClient) {
            $client->restart();
        }
    }
}
