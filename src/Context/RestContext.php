<?php

declare(strict_types=1);

namespace TwentytwoLabs\Behat\OpenApi\Context;

use Behat\Gherkin\Node\PyStringNode;
use Behat\Gherkin\Node\TableNode;
use TwentytwoLabs\Behat\OpenApi\Client\GuzzleClient;

/**
 * Class RestContext.
 */
class RestContext extends BaseContext
{
    private ?GuzzleClient $client = null;

    /**
     * Sends a HTTP request
     *
     * @Given I send a :method request to :url
     */
    public function iSendARequestTo($method, $url, PyStringNode $body = null, $files = [])
    {
        return $this->getClient()->send(
            $method,
            $this->locatePath($url),
            [],
            $files,
            $body !== null ? $body->getRaw() : null
        );
    }

    /**
     * Sends a HTTP request with a some parameters
     *
     * @Given I send a :method request to :url with parameters:
     */
    public function iSendARequestToWithParameters($method, $url, TableNode $data)
    {
        $files = [];
        $parameters = [];

        foreach ($data->getHash() as $row) {
            if (!isset($row['key']) || !isset($row['value'])) {
                throw new \Exception("You must provide a 'key' and 'value' column in your table node.");
            }

            if (is_string($row['value']) && substr($row['value'], 0, 1) == '@') {
                $files[$row['key']] = rtrim($this->getMinkParameter('files_path'), DIRECTORY_SEPARATOR).DIRECTORY_SEPARATOR.substr($row['value'],1);
            }
            else {
                $parameters[$row['key']] = $row['value'];
            }
        }

        return $this->getClient()->send(
            $method,
            $this->locatePath($url),
            $parameters,
            $files
        );
    }

    /**
     * Sends a HTTP request with a body
     *
     * @Given I send a :method request to :url with body:
     */
    public function iSendARequestToWithBody($method, $url, PyStringNode $body)
    {
        return $this->iSendARequestTo($method, $url, $body);
    }

    /**
     * Checks, whether the response content is equal to given text
     *
     * @Then the response should be equal to
     * @Then the response should be equal to:
     */
    public function theResponseShouldBeEqualTo(PyStringNode $expected)
    {
        $expected = str_replace('\\"', '"', $expected);
        $actual = $this->getClient()->getResponseContent();

        $this->assertEquals(
            $expected,
            $actual,
            sprintf('Actual response is [%s], but expected [%s]', $actual, $expected)
        );
    }

    /**
     * Checks, whether the response content is null or empty string
     *
     * @Then the response should be empty
     */
    public function theResponseShouldBeEmpty()
    {
        $actual = $this->getClient()->getResponseContent();
        $this->assertTrue(
            empty($actual),
            sprintf('The response of the current page is not empty, it is: %s', $actual)
        );
    }

    /**
     * Checks, whether the header name is equal to given text
     *
     * @Then the header :name should be equal to :value
     */
    public function theHeaderShouldBeEqualTo($name, $value)
    {
        $actual = $this->getClient()->getResponseHeader($name);

        $this->assertEquals(
            strtolower($value),
            strtolower($actual),
            sprintf('The header [%s] should be equal to [%s], but it is: [%s]', $name, $value, $actual)
        );
    }

    /**
     * Checks, whether the header name is not equal to given text
     *
     * @Then the header :name should not be equal to :value
     */
    public function theHeaderShouldNotBeEqualTo($name, $value)
    {
        $actual = $this->getClient()->getResponseHeader($name);

        if (strtolower($value) === strtolower($actual)) {
            throw new \Exception(sprintf('The header [%s] is equal to %s', $name, $actual));
        }
    }

    /**
     * Checks, whether the header name contains the given text
     *
     * @Then the header :name should contain :value
     */
    public function theHeaderShouldContain($name, $value)
    {
        $actual = $this->getClient()->getResponseHeader($name);

        $this->assertContains(
            $value,
            $actual,
            sprintf('The header [%s] should contain value [%s] but actual value is [%s]', $name, $value, $actual)
        );
    }

    /**
     * Checks, whether the header name doesn't contain the given text
     *
     * @Then the header :name should not contain :value
     */
    public function theHeaderShouldNotContain($name, $value)
    {
        $this->assertNotContains(
            $value,
            $this->getClient()->getResponseHeader($name),
            sprintf('The header [%s] contains [%s]', $name, $value)
        );
    }

    /**
     * Checks, whether the header not exist
     *
     * @Then the header :name should not exist
     */
    public function theHeaderShouldNotExist($name)
    {
        $this->not(
            function () use($name) {
                $this->theHeaderShouldExist($name);
            },
            sprintf('The header [%s] exists', $name)
        );
    }

    protected function theHeaderShouldExist($name)
    {
        return $this->getClient()->getResponseHeader($name);
    }

    /**
     * @Then the header :name should match :regex
     */
    public function theHeaderShouldMatch($name, $regex)
    {
        $actual = $this->getClient()->getResponseHeader($name);

        $this->assertEquals(
            1,
            preg_match($regex, $actual),
            "The header '$name' should match '$regex', but it is: '$actual'"
        );
    }

    /**
     * @Then the header :name should not match :regex
     */
    public function theHeaderShouldNotMatch($name, $regex)
    {
        $this->not(
            function () use ($name, $regex) {
                $this->theHeaderShouldMatch($name, $regex);
            },
            "The header '$name' should not match '$regex'"
        );
    }

    /**
     * Checks, that the response header expire is in the future
     *
     * @Then the response should expire in the future
     */
    public function theResponseShouldExpireInTheFuture()
    {
        $date = new \DateTime($this->getClient()->getResponseRawHeader('Date')[0]);
        $expires = new \DateTime($this->getClient()->getResponseRawHeader('Expires')[0]);

        $this->assertSame(
            1,
            $expires->diff($date)->invert,
            sprintf('The response doesn\'t expire in the future (%s)', $expires->format(DATE_ATOM))
        );
    }

    /**
     * Add an header element in a request
     *
     * @Then I add :name header equal to :value
     */
    public function iAddHeaderEqualTo($name, $value)
    {
        $this->getClient()->setRequestHeader($name, $value);
    }

    /**
     * @Then the response should be encoded in :encoding
     */
    public function theResponseShouldBeEncodedIn($encoding)
    {
        $content = $this->getClient()->getResponseContent();
        if (!mb_check_encoding($content, $encoding)) {
            throw new \Exception("The response is not encoded in $encoding");
        }

        $this->theHeaderShouldContain('Content-Type', "charset=$encoding");
    }

    /**
     * @Then print last response headers
     */
    public function printLastResponseHeaders()
    {
        $text = '';
        $headers = $this->getClient()->getResponseHeaders();

        foreach ($headers as $name => $value) {
            $text .= $name . ': '. $this->getClient()->getResponseHeader($name) . "\n";
        }
        echo $text;
    }

    public function getClient(): GuzzleClient
    {
        if (null === $this->client) {
            $this->client = new GuzzleClient();
        }

        return $this->client;
    }

    private function locatePath(string $path): string
    {
        $startUrl = rtrim($this->getMinkParameter('base_url') ?? '', '/') . '/';

        return 0 !== strpos($path, 'http') ? $startUrl . ltrim($path, '/') : $path;
    }
}
