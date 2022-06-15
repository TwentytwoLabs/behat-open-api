<?php

declare(strict_types=1);

namespace TwentytwoLabs\BehatOpenApiExtension\Context;

/**
 * Class DebugContext.
 */
class DebugContext extends RawRestContext
{
    /**
     * @Then print last response headers
     */
    public function printLastResponseHeaders()
    {
        foreach ($this->getResponseHeaders() as $name => $value) {
            echo sprintf('%s: %s%s', $name, implode(' ', $value), PHP_EOL);
        }
    }

    /**
     * @Then print profiler link
     */
    public function displayProfilerLink()
    {
        echo sprintf('The debug profile URL [%s]', $this->getResponseHeader('X-Debug-Token-Link'));
    }

    /**
     * @Then print last JSON response
     */
    public function printLastJsonResponse()
    {
        echo $this->getContent();
    }

    /**
     * @Then print execution time
     */
    public function printExecutionTime()
    {
        if (null === $this->time) {
            throw new \Exception(sprintf('You must send a HTTP request before print execution time'));
        }

        echo sprintf('The respond has been %s seconds', $time->format("%f") / 1000);
    }
}
