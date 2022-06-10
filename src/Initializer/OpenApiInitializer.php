<?php

declare(strict_types=1);

namespace TwentytwoLabs\BehatOpenApiExtension\Initializer;

use Behat\Behat\Context\Context;
use Behat\Behat\Context\Initializer\ContextInitializer;
use TwentytwoLabs\Api\Validator\MessageValidator;
use TwentytwoLabs\BehatOpenApiExtension\Context\OpenApiContext;

/**
 * class OpenApiInitializer.
 */
class OpenApiInitializer implements ContextInitializer
{
    private ?string $schemaFile;
    private MessageValidator $validator;

    public function __construct(?string $schemaFile, MessageValidator $validator)
    {
        $this->schemaFile = $schemaFile;
        $this->validator = $validator;
    }

    public function initializeContext(Context $context)
    {
        if ($context instanceof OpenApiContext) {
            $context
                ->setSchemaFile($this->schemaFile)
                ->setValidator($this->validator)
            ;
        }
    }
}
