<?php

declare(strict_types=1);

namespace TwentytwoLabs\BehatOpenApiExtension\Handler;

/**
 * Interface HandlerInterface.
 */
interface HandlerInterface
{
    public function setRequestHeader(string $name, string $value): self;
    public function send(string $method, string $url, array $headers = [], ?string $content = null);
    public function getResponseHeaders(): array;
    public function getResponseHeader($name): string;
    public function getStatusCode(): int;
    public function getResponseContent(): string;
}
