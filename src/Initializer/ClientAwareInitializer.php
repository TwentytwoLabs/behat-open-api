<?php

declare(strict_types=1);

namespace TwentytwoLabs\BehatOpenApiExtension\Initializer;

use Behat\Behat\Context\Context;
use Behat\Behat\Context\Initializer\ContextInitializer;
use TwentytwoLabs\BehatOpenApiExtension\Context\ClientAwareInterface;
use TwentytwoLabs\BehatOpenApiExtension\Handler\GuzzleHandler;

/**
 * class ClientAwareInitializer.
 */
class ClientAwareInitializer implements ContextInitializer
{
    private GuzzleHandler $client;

    public function __construct(GuzzleHandler $client)
    {
        $this->client = $client;
    }

    public function initializeContext(Context $context)
    {
        if (!$context instanceof ClientAwareInterface) {
            return;
        }

        $context->setClient($this->client);
    }
}
