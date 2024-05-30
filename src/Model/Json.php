<?php

declare(strict_types=1);

namespace TwentytwoLabs\BehatOpenApiExtension\Model;

use Symfony\Component\PropertyAccess\PropertyAccessor;

final class Json
{
    protected mixed $content;

    public function __construct(string $content)
    {
        $this->content = $this->decode($content);
    }

    public function getContent(): mixed
    {
        return $this->content;
    }

    public function read(string $expression, PropertyAccessor $accessor): mixed
    {
        $expression = preg_replace('/^root\./', '', trim($expression));

        // If root asked, we return the entire content
        if (strlen($expression) <= 0) {
            return $this->content;
        }

        return $accessor->getValue($this->content, $expression);
    }

    public function encode(): bool|string
    {
        return json_encode($this->content, JSON_PRETTY_PRINT);
    }

    public function __toString(): string
    {
        return $this->encode();
    }

    private function decode(string $content): mixed
    {
        $result = json_decode($content);

        if (JSON_ERROR_NONE !== json_last_error()) {
            throw new \Exception("The string '$content' is not valid json");
        }

        return $result;
    }
}
