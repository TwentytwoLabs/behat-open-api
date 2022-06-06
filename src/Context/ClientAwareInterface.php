<?php

declare(strict_types=1);


namespace TwentytwoLabs\BehatOpenApiExtension\Context;


use TwentytwoLabs\BehatOpenApiExtension\Handler\GuzzleHandler;

/**
 * Interface ClientAwareInterface.
 */
interface ClientAwareInterface
{
    public function setClient(GuzzleHandler $client): self;
}
