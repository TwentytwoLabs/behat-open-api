<?php

declare(strict_types=1);


namespace TwentytwoLabs\BehatOpenApiExtension\Handler;


use Behat\Mink\Mink;

/**
 * class MinkWrapper.
 */
class MinkHandler implements HandlerInterface
{
    private Mink $mink;

    public function __construct(Mink $mink)
    {
        $this->mink = $mink;
    }

    public function setRequestHeader(string $name, string $value): HandlerInterface
    {
        $this->mink->getSession()->getDriver()->setRequestHeader($name, $value);

        return $this;
    }

    public function send(string $method, string $url, array $headers = [], ?string $content = null)
    {
        $this->mink->getSession()->getDriver()->getContent();
    }

    public function getResponseHeaders(): array
    {
        // TODO: Implement getResponseHeaders() method.
    }

    public function getResponseHeader($name): string
    {
        return '';
    }

    public function getStatusCode(): int
    {
        return 200;
    }

    public function getResponseContent(): string
    {
        return '';
    }
}
