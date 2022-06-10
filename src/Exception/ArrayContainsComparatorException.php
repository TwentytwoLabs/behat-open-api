<?php

declare(strict_types=1);

namespace TwentytwoLabs\BehatOpenApiExtension\Exception;

/**
 * class ArrayContainsComparatorException.
 */
class ArrayContainsComparatorException extends \Exception
{
    public function __construct(string $message, int $code = 0, \Exception $previous = null, $needle = null, $haystack = null)
    {
        $message .= PHP_EOL.PHP_EOL;
        $message .= '================================================================================'.PHP_EOL;
        $message .= '= Needle ======================================================================='.PHP_EOL;
        $message .= '================================================================================'.PHP_EOL;
        $message .= json_encode($needle, JSON_PRETTY_PRINT).PHP_EOL;
        $message .= '================================================================================'.PHP_EOL;
        $message .= '= Haystack ====================================================================='.PHP_EOL;
        $message .= '================================================================================'.PHP_EOL;
        $message .= json_encode($haystack, JSON_PRETTY_PRINT).PHP_EOL;

        parent::__construct($message, $code, $previous);
    }
}
