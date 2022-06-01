<?php

declare(strict_types=1);

namespace TwentytwoLabs\Behat\OpenApi\Context;

use Behat\Behat\Context\Context;
use Behat\Behat\Hook\Scope\BeforeScenarioScope;

/**
 * Class BaseContext.
 */
abstract class BaseContext implements Context
{
    protected function not(callable $callbable, $errorMessage)
    {
        try {
            $callbable();
        }
        catch (\Exception $e) {
            return;
        }

        throw new \Exception($errorMessage);
    }

    protected function assert($test, $message)
    {
        if ($test === false) {
            throw new \Exception($message);
        }
    }

    protected function assertContains($expected, $actual, $message = null)
    {
        $regex   = '/' . preg_quote($expected, '/') . '/ui';

        $this->assert(preg_match($regex, $actual) > 0, $message ?: "The string '$expected' was not found.");
    }

    protected function assertNotContains($expected, $actual, $message = null)
    {
        $message = $message ?: "The string '$expected' was found.";

        $this->not(function () use($expected, $actual) {
            $this->assertContains($expected, $actual);
        }, $message);
    }

    protected function assertCount($expected, array $elements, $message = null)
    {
        $this->assert(
            intval($expected) === count($elements),
            $message ?: sprintf('%d elements found, but should be %d.', count($elements), $expected)
        );
    }

    protected function assertEquals($expected, $actual, $message = null)
    {
        $this->assert(
            $expected == $actual,
            $message ?: "The element '$actual' is not equal to '$expected'"
        );
    }

    protected function assertSame($expected, $actual, $message = null)
    {
        $this->assert(
            $expected === $actual,
            $message ?: "The element '$actual' is not equal to '$expected'"
        );
    }

    protected function assertArrayHasKey($key, $array, $message = null)
    {
        $this->assert(
            isset($array[$key]),
            $message ?: "The array has no key '$key'"
        );
    }

    protected function assertArrayNotHasKey($key, $array, $message = null)
    {
        $message = $message ?: "The array has key '$key'";

        $this->not(function () use($key, $array) {
            $this->assertArrayHasKey($key, $array);
        }, $message);
    }

    protected function assertTrue($value, $message = 'The value is false')
    {
        $this->assert($value, $message);
    }

    protected function assertFalse($value, $message = 'The value is true')
    {
        $this->not(function () use($value) {
            $this->assertTrue($value);
        }, $message);
    }

    protected function assertRegex(string $regex, string $actual)
    {
        if (0 === preg_match($regex, $actual)) {
            throw new \Exception(sprintf("The node value is '%s'", json_encode($actual)));
        }
    }
}
