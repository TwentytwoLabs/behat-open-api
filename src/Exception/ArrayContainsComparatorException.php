<?php

declare(strict_types=1);

namespace TwentytwoLabs\BehatOpenApiExtension\Exception;

final class ArrayContainsComparatorException extends \Exception
{
    /**
     * @param array<int|string, mixed> $needle
     * @param array<int|string, mixed> $haystack
     */
    public function __construct(
        string $message,
        int $code = 0,
        ?\Exception $previous = null,
        array $needle = [],
        array $haystack = []
    ) {
        $message .= sprintf('%s%s', PHP_EOL, PHP_EOL);
        $message .= sprintf('=============================================================================%s', PHP_EOL);
        $message .= sprintf('= Needle ====================================================================%s', PHP_EOL);
        $message .= sprintf('=============================================================================%s', PHP_EOL);
        $message .= sprintf('%s%s', json_encode($needle, JSON_PRETTY_PRINT), PHP_EOL);
        $message .= sprintf('=============================================================================%s', PHP_EOL);
        $message .= sprintf('= Haystack ==================================================================%s', PHP_EOL);
        $message .= sprintf('=============================================================================%s', PHP_EOL);
        $message .= sprintf('%s%s', json_encode($haystack, JSON_PRETTY_PRINT), PHP_EOL);

        parent::__construct($message, $code, $previous);
    }
}
