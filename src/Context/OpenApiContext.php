<?php

declare(strict_types=1);

namespace TwentytwoLabs\BehatOpenApiExtension\Context;

use GuzzleHttp\Psr7\Response;
use Psr\Http\Message\ResponseInterface;
use TwentytwoLabs\Api\Factory\SwaggerSchemaFactory;
use TwentytwoLabs\Api\Schema;
use TwentytwoLabs\Api\Validator\Exception\ConstraintViolations;
use TwentytwoLabs\Api\Validator\MessageValidator;

/**
 * class OpenApiContext.
 */
class OpenApiContext extends RawRestContext
{
    private MessageValidator $validator;
    private ?Schema $schema = null;

    /**
     * @Then the response should be valid according to the operation id :arg1
     */
    public function theResponseShouldBeValidAccordingToTheOperationId($arg1)
    {
        $requestDefinition = $this->getSchema()->getRequestDefinition($arg1);

        $this->validator->validateResponse($this->buildPsr7Request(), $requestDefinition);

        if ($this->validator->hasViolations()) {
            throw new ConstraintViolations($this->validator->getViolations());
        }
    }

    public function setValidator(MessageValidator $validator): self
    {
        $this->validator = $validator;

        return $this;
    }

    public function setSchemaFile(?string $schemaFile): self
    {
        if (null !== $schemaFile) {
            $this->schema = (new SwaggerSchemaFactory())->createSchema($schemaFile);
        }

        return $this;
    }

    private function getSchema(): Schema
    {
        if (null === $this->schema) {
            throw new \InvalidArgumentException('You want to check OpenApi operation without schema');
        }

        return $this->schema;
    }

    private function buildPsr7Request(): ResponseInterface
    {
        return new Response(
            $this->getSession()->getStatusCode(),
            $this->getResponseHeaders(),
            $this->getContent()
        );
    }
}
