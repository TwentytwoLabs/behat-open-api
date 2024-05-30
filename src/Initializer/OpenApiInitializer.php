<?php

declare(strict_types=1);

namespace TwentytwoLabs\BehatOpenApiExtension\Initializer;

use Behat\Behat\Context\Context;
use Behat\Behat\Context\Initializer\ContextInitializer;
use TwentytwoLabs\ApiValidator\Validator\MessageValidator;
use TwentytwoLabs\BehatOpenApiExtension\Context\OpenApiContext;

final class OpenApiInitializer implements ContextInitializer
{
    private MessageValidator $validator;
    private ?string $schemaFile;

    public function __construct(MessageValidator $validator, ?string $schemaFile)
    {
        $this->validator = $validator;
        $this->schemaFile = $schemaFile;
    }

    public function initializeContext(Context $context): void
    {
        if ($context instanceof OpenApiContext) {
            $context
                ->setSchemaFile($this->schemaFile)
                ->setValidator($this->validator)
            ;
        }
    }
}
