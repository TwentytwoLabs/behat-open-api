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
}
