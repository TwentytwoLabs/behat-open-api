<?php

declare(strict_types=1);

namespace TwentytwoLabs\BehatOpenApiExtension\Context;

use Behat\Gherkin\Node\PyStringNode;
use Behat\Gherkin\Node\TableNode;
use Symfony\Component\PropertyAccess\PropertyAccessor;
use TwentytwoLabs\ArrayComparator\AsserterTrait as ArrayComparatorAsserterTrait;
use TwentytwoLabs\BehatOpenApiExtension\Exception\ArrayContainsComparatorException;
use TwentytwoLabs\BehatOpenApiExtension\Model\Json;
use Webmozart\Assert\Assert;

final class JsonContext extends RawRestContext
{
    use ArrayComparatorAsserterTrait;

    /**
     * Checks, that the response is correct JSON.
     *
     * @Then the response should be in JSON
     */
    public function theResponseShouldBeInJson(): void
    {
        Assert::regex($this->getResponseHeader('Content-Type'), '/json/ui');
        $this->getJson();
    }

    /**
     * Checks, that the response is not correct JSON.
     *
     * @Then the response should not be in JSON
     */
    public function theResponseShouldNotBeInJson(): void
    {
        Assert::notRegex($this->getResponseHeader('Content-Type'), '/json/ui', 'The response is in JSON');

        try {
            $this->getJson();
        } catch (\Exception) {
            return;
        }

        throw new \Exception('The response is in JSON');
    }

    /**
     * Checks, that given JSON node is equal to given value.
     *
     * @Then the JSON node :node should be equal to :text
     */
    public function theJsonNodeShouldBeEqualTo(string $node, string $text): void
    {
        $json = $this->getJson();

        $actual = $this->evaluate($json, $node);

        Assert::same($text, $actual, sprintf("The node value is '%s'", json_encode($actual)));
    }

    /**
     * Checks, that given JSON nodes are equal to givens values.
     *
     * @Then the JSON nodes should be equal to:
     */
    public function theJsonNodesShouldBeEqualTo(TableNode $nodes): void
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
    public function theJsonNodeShouldMatch(string $node, string $pattern): void
    {
        $json = $this->getJson();

        $actual = $this->evaluate($json, $node);

        Assert::regex($actual, $pattern, sprintf("The node value is '%s'", json_encode($actual)));
    }

    /**
     * Checks, that given JSON node is null.
     *
     * @Then the JSON node :node should be null
     */
    public function theJsonNodeShouldBeNull(string $node): void
    {
        $json = $this->getJson();

        $actual = $this->evaluate($json, $node);

        Assert::null($actual, sprintf('The node value is `%s`', json_encode($actual)));
    }

    /**
     * Checks, that given JSON node is not null.
     *
     * @Then the JSON node :node should not be null
     */
    public function theJsonNodeShouldNotBeNull(string $node): void
    {
        $json = $this->getJson();

        $actual = $this->evaluate($json, $node);

        Assert::notNull($actual, sprintf('The node %s should not be null', json_encode($actual)));
    }

    /**
     * Checks, that given JSON node is true.
     *
     * @Then the JSON node :node should be true
     */
    public function theJsonNodeShouldBeTrue(string $node): void
    {
        $json = $this->getJson();

        $actual = $this->evaluate($json, $node);

        Assert::true($actual, sprintf('The node value is `%s`', json_encode($actual)));
    }

    /**
     * Checks, that given JSON node is false.
     *
     * @Then the JSON node :node should be false
     */
    public function theJsonNodeShouldBeFalse(string $node): void
    {
        $json = $this->getJson();

        $actual = $this->evaluate($json, $node);

        Assert::false($actual, sprintf('The node value is `%s`', json_encode($actual)));
    }

    /**
     * Checks, that given JSON node is equal to the given number.
     *
     * @Then the JSON node :node should be equal to the number :number
     */
    public function theJsonNodeShouldBeEqualToTheNumber(string $node, string $number): void
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
    public function theJsonNodeShouldHaveElements(string $node, int $count): void
    {
        $json = $this->getJson();

        $actual = $this->evaluate($json, $node);

        Assert::count((array) $actual, $count);
    }

    /**
     * Checks, that given JSON node contains given value.
     *
     * @Then the JSON node :node should contain :text
     */
    public function theJsonNodeShouldContain(string $node, string $text): void
    {
        $json = $this->getJson();

        $actual = $this->evaluate($json, $node);

        Assert::regex($actual, sprintf('/%s/ui', preg_quote($text, '/')));
    }

    /**
     * Checks, that given JSON nodes contains values.
     *
     * @Then the JSON nodes should contain:
     */
    public function theJsonNodesShouldContain(TableNode $nodes): void
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
    public function theJsonNodeShouldNotContain(string $node, string $text): void
    {
        $json = $this->getJson();

        $actual = $this->evaluate($json, $node);

        Assert::notContains($actual, $text);
    }

    /**
     * Checks, that given JSON nodes does not contain given value.
     *
     * @Then the JSON nodes should not contain:
     */
    public function theJsonNodesShouldNotContain(TableNode $nodes): void
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
    public function theJsonNodeShouldExist(string $name): mixed
    {
        try {
            $json = $this->getJson();

            return $this->evaluate($json, $name);
        } catch (\Exception) {
            throw new \Exception("The node '$name' does not exist.");
        }
    }

    /**
     * Checks, that given JSON node does not exist.
     *
     * @Then the JSON node :name should not exist
     */
    public function theJsonNodeShouldNotExist(string $name): void
    {
        try {
            $json = $this->getJson();
            $this->evaluate($json, $name);
        } catch (\Exception) {
            return;
        }

        throw new \Exception("The node '$name' exists.");
    }

    /**
     * @Then the JSON should be equal to:
     */
    public function theJsonShouldBeEqualTo(PyStringNode $content): void
    {
        try {
            $actual = $this->getJson();
            $expected = new Json((string) $content);
        } catch (\Exception) {
            throw new \Exception('The expected JSON is not a valid');
        }

        Assert::same((string) $actual, (string) $expected, sprintf("The json is equal to:\n%s", $actual->encode()));
    }

    /**
     * @Then the JSON node :node should have key :
     */
    public function assertTableColumns(string $node, TableNode $columns): void
    {
        $json = $this->getJson();

        $actual = $this->evaluate($json, $node);

        $first = json_decode(json_encode(current($actual)), true);

        $key = array_keys($first);
        $columns = current($columns->getRows());

        try {
            $this->assertKeysOfJson($key, $columns);
        } catch (\Exception $e) {
            $e = new ArrayContainsComparatorException(
                message: $e->getMessage(),
                previous: $e,
                needle: $columns,
                haystack: $key
            );

            throw $e;
        }
    }

    /**
     * @Then the JSON should be match to:
     */
    public function theJsonShouldBeMatchTo(PyStringNode $string): void
    {
        $expectedItem = json_decode($string->getRaw(), true) ?? [];
        $item = json_decode(json_encode($this->getJson()->getContent()), true);

        try {
            $this->assertKeysOfJson(array_keys($expectedItem), array_keys($item));
            $this->assertValuesOfJson($expectedItem, $item);
        } catch (\Exception $e) {
            $e = new ArrayContainsComparatorException(
                message: $e->getMessage(),
                previous: $e,
                needle: $expectedItem,
                haystack: $item
            );

            throw $e;
        }
    }

    private function getJson(): Json
    {
        return new Json($this->getContent());
    }

    private function evaluate(Json $json, string $expression): mixed
    {
        $expression = str_replace('->', '.', $expression);

        try {
            return $json->read($expression, new PropertyAccessor());
        } catch (\Exception) {
            throw new \Exception(sprintf('Failed to evaluate expression %s', $expression));
        }
    }
}
