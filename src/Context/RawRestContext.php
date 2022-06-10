<?php

declare(strict_types=1);

namespace TwentytwoLabs\BehatOpenApiExtension\Context;

use Behat\MinkExtension\Context\RawMinkContext;

/**
 * class RawRestContext.
 */
abstract class RawRestContext extends RawMinkContext
{
    protected function getClient()
    {
        return $this->getSession()->getDriver()->getClient();
    }

    protected function getResponseHeaders()
    {
        return $this->getSession()->getResponseHeaders();
    }

    protected function getResponseHeader(string $name)
    {
        return $this->getSession()->getResponseHeader($name);
    }

    protected function getContent()
    {
        return $this->getSession()->getDriver()->getContent();
    }
}
