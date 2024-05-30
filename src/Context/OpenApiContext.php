<?php

declare(strict_types=1);

namespace TwentytwoLabs\BehatOpenApiExtension\Context;

use GuzzleHttp\Psr7\Response;
use Psr\Http\Message\ResponseInterface;
use TwentytwoLabs\ApiValidator\Factory\OpenApiSchemaFactory;
use TwentytwoLabs\ApiValidator\Schema;
use TwentytwoLabs\ApiValidator\Validator\ConstraintViolation;
use TwentytwoLabs\ApiValidator\Validator\MessageValidator;

final class OpenApiContext extends RawRestContext
{
    private MessageValidator $validator;
    private ?Schema $schema = null;

    /**
     * @Then the response should be valid according to the operation id :arg1
     */
    public function theResponseShouldBeValidAccordingToTheOperationId(string $arg1): void
    {
        $requestDefinition = $this->getSchema()->getOperationDefinition($arg1);

        $this->validator->validateResponse($this->buildPsr7Response(), $requestDefinition);
        if ($this->validator->hasViolations()) {
            $items = array_map(fn (ConstraintViolation $item) => $item->toArray(), $this->validator->getViolations());

            throw new \Exception(sprintf('%s%s', json_encode($items, JSON_PRETTY_PRINT), PHP_EOL));
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
            $factory = new OpenApiSchemaFactory();
            $this->schema = $factory->createSchema($schemaFile);
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

    private function buildPsr7Response(): ResponseInterface
    {
        return new Response(
            $this->getSession()->getStatusCode(),
            $this->getResponseHeaders(),
            $this->getContent()
        );
    }
}
