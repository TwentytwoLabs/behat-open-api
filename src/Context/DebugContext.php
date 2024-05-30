<?php

declare(strict_types=1);

namespace TwentytwoLabs\BehatOpenApiExtension\Context;

final class DebugContext extends RawRestContext
{
    /**
     * @Then print last response headers
     */
    public function printLastResponseHeaders(): void
    {
        foreach ($this->getResponseHeaders() as $name => $value) {
            echo sprintf('%s: %s%s', $name, implode(' ', $value), PHP_EOL);
        }
    }

    /**
     * @Then print last JSON response
     */
    public function printLastJsonResponse(): void
    {
        echo $this->getContent();
    }

    /**
     * @Then save last JSON response in :file
     */
    public function saveLastJsonResponseIn(string $file): void
    {
        file_put_contents($file, $this->getContent());
    }
}
