<?php

declare(strict_types=1);

namespace TwentytwoLabs\BehatOpenApiExtension\Context;

use Behat\Gherkin\Node\PyStringNode;
use Behat\Gherkin\Node\TableNode;
use Symfony\Component\PropertyAccess\PropertyAccessor;
use TwentytwoLabs\ArrayComparator\AsserterTrait as ArrayComparatorAsserterTrait;
use TwentytwoLabs\BehatOpenApiExtension\AsserterTrait;
use TwentytwoLabs\BehatOpenApiExtension\Exception\ArrayContainsComparatorException;
use TwentytwoLabs\BehatOpenApiExtension\Model\Json;

/**
 * Class JsonContext.
 */
class JsonContext extends RawRestContext
{
    use AsserterTrait;
    use ArrayComparatorAsserterTrait;

    /**
     * Checks, that the response is correct JSON.
     *
     * @Then the response should be in JSON
     */
    public function theResponseShouldBeInJson()
    {
        $this->assertContains('json', $this->getResponseHeader('Content-Type'));
        $this->getJson();
    }

    /**
     * Checks, that the response is not correct JSON.
     *
     * @Then the response should not be in JSON
     */
    public function theResponseShouldNotBeInJson()
    {
        $this->not([$this, 'theResponseShouldBeInJson'], 'The response is in JSON');
    }

    /**
     * Checks, that given JSON node is equal to given value.
     *
     * @Then the JSON node :node should be equal to :text
     */
    public function theJsonNodeShouldBeEqualTo($node, $text)
    {
        $json = $this->getJson();

        $actual = $this->evaluate($json, $node);

        if ($actual !== $text) {
            throw new \Exception(sprintf("The node value is '%s'", json_encode($actual)));
        }
    }

    /**
     * Checks, that given JSON nodes are equal to givens values.
     *
     * @Then the JSON nodes should be equal to:
     */
    public function theJsonNodesShouldBeEqualTo(TableNode $nodes)
    {
        foreach ($nodes->getRowsHash() as $node => $text) {
            $this->theJsonNodeShouldBeEqualTo($node, $text);
        }
    }

    /**
     * Checks, that given JSON node matches given pattern.
     *
     * @Then the JSON node :node should match :pattern
     */
    public function theJsonNodeShouldMatch($node, $pattern)
    {
        $json = $this->getJson();

        $actual = $this->evaluate($json, $node);

        if (0 === preg_match($pattern, $actual)) {
            throw new \Exception(sprintf("The node value is '%s'", json_encode($actual)));
        }
    }

    /**
     * Checks, that given JSON node is null.
     *
     * @Then the JSON node :node should be null
     */
    public function theJsonNodeShouldBeNull($node)
    {
        $json = $this->getJson();

        $actual = $this->evaluate($json, $node);

        if (null !== $actual) {
            throw new \Exception(sprintf('The node value is `%s`', json_encode($actual)));
        }
    }

    /**
     * Checks, that given JSON node is not null.
     *
     * @Then the JSON node :node should not be null
     */
    public function theJsonNodeShouldNotBeNull($node)
    {
        $this->not(function () use ($node) {
            $this->theJsonNodeShouldBeNull($node);
        }, sprintf('The node %s should not be null', $node));
    }

    /**
     * Checks, that given JSON node is true.
     *
     * @Then the JSON node :node should be true
     */
    public function theJsonNodeShouldBeTrue($node)
    {
        $json = $this->getJson();

        $actual = $this->evaluate($json, $node);

        $this->assertTrue($actual, sprintf('The node value is `%s`', json_encode($actual)));
    }

    /**
     * Checks, that given JSON node is false.
     *
     * @Then the JSON node :node should be false
     */
    public function theJsonNodeShouldBeFalse($node)
    {
        $json = $this->getJson();

        $actual = $this->evaluate($json, $node);

        $this->assertFalse($actual, sprintf('The node value is `%s`', json_encode($actual)));
    }

    /**
     * Checks, that given JSON node is equal to the given string.
     *
     * @Then the JSON node :node should be equal to the string :text
     */
    public function theJsonNodeShouldBeEqualToTheString($node, $text)
    {
        $json = $this->getJson();

        $actual = $this->evaluate($json, $node);

        if ($actual !== $text) {
            throw new \Exception(sprintf('The node value is `%s`', json_encode($actual)));
        }
    }

    /**
     * Checks, that given JSON node is equal to the given number.
     *
     * @Then the JSON node :node should be equal to the number :number
     */
    public function theJsonNodeShouldBeEqualToTheNumber($node, $number)
    {
        $json = $this->getJson();

        $actual = $this->evaluate($json, $node);

        if ($actual !== (float) $number && $actual !== (int) $number) {
            throw new \Exception(sprintf('The node value is `%s`', json_encode($actual)));
        }
    }

    /**
     * Checks, that given JSON node has N element(s).
     *
     * @Then the JSON node :node should have :count element(s)
     */
    public function theJsonNodeShouldHaveElements(string $node, int $count)
    {
        $json = $this->getJson();

        $actual = $this->evaluate($json, $node);

        $this->assertSame($count, sizeof((array) $actual));
    }

    /**
     * Checks, that given JSON node contains given value.
     *
     * @Then the JSON node :node should contain :text
     */
    public function theJsonNodeShouldContain($node, $text)
    {
        $json = $this->getJson();

        $actual = $this->evaluate($json, $node);

        $this->assertContains($text, (string) $actual);
    }

    /**
     * Checks, that given JSON nodes contains values.
     *
     * @Then the JSON nodes should contain:
     */
    public function theJsonNodesShouldContain(TableNode $nodes)
    {
        foreach ($nodes->getRowsHash() as $node => $text) {
            $this->theJsonNodeShouldContain($node, $text);
        }
    }

    /**
     * Checks, that given JSON node does not contain given value.
     *
     * @Then the JSON node :node should not contain :text
     */
    public function theJsonNodeShouldNotContain($node, $text)
    {
        $json = $this->getJson();

        $actual = $this->evaluate($json, $node);

        $this->assertNotContains($text, (string) $actual);
    }

    /**
     * Checks, that given JSON nodes does not contain given value.
     *
     * @Then the JSON nodes should not contain:
     */
    public function theJsonNodesShouldNotContain(TableNode $nodes)
    {
        foreach ($nodes->getRowsHash() as $node => $text) {
            $this->theJsonNodeShouldNotContain($node, $text);
        }
    }

    /**
     * Checks, that given JSON node exist.
     *
     * @Then the JSON node :name should exist
     */
    public function theJsonNodeShouldExist($name)
    {
        $json = $this->getJson();

        try {
            $node = $this->evaluate($json, $name);
        } catch (\Exception $e) {
            throw new \Exception("The node '$name' does not exist.");
        }

        return $node;
    }

    /**
     * Checks, that given JSON node does not exist.
     *
     * @Then the JSON node :name should not exist
     */
    public function theJsonNodeShouldNotExist($name)
    {
        $this->not(function () use ($name) {
            return $this->theJsonNodeShouldExist($name);
        }, "The node '$name' exists.");
    }

    /**
     * @Then the JSON should be equal to:
     */
    public function theJsonShouldBeEqualTo(PyStringNode $content)
    {
        $actual = $this->getJson();

        try {
            $expected = new Json($content);
        } catch (\Exception $e) {
            throw new \Exception('The expected JSON is not a valid');
        }

        $this->assertSame((string) $expected, (string) $actual, "The json is equal to:\n".$actual->encode());
    }

    /**
     * @Then the JSON node :node should have key :
     */
    public function assertTableColumns(string $node, TableNode $columns)
    {
        $json = $this->getJson();

        $actual = $this->evaluate($json, $node);
        $first = json_decode(json_encode(reset($actual)), true);

        $key = array_keys($first);
        $columns = $columns->getRows()[0];

        try {
            $this->assertKeysOfJson($key, $columns);
        } catch (\Exception $e) {
            throw new ArrayContainsComparatorException($e->getMessage(), 0, $e, $columns, $key);
        }
    }

    /**
     * @Then the JSON should be match to:
     */
    public function theJsonShouldBeMatchTo(PyStringNode $string)
    {
        $expectedItem = json_decode($string->getRaw(), true) ?? [];
        $item = json_decode(json_encode($this->getJson()->getContent()), true);

        try {
            $this->assertKeysOfJson(array_keys($expectedItem), array_keys($item));
            $this->assertValuesOfJson($expectedItem, $item);
        } catch (\Exception $e) {
            throw new ArrayContainsComparatorException($e->getMessage(), 0, $e, $expectedItem, $item);
        }
    }

    protected function getJson(): Json
    {
        return new Json($this->getContent());
    }

    private function evaluate(Json $json, $expression)
    {
        $expression = str_replace('->', '.', $expression);

        try {
            return $json->read($expression, new PropertyAccessor());
        } catch (\Exception $ex) {
            throw new \Exception(sprintf('Failed to evaluate expression %s', $expression));
        }
    }
}
