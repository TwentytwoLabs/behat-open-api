<?php

declare(strict_types=1);

namespace TwentytwoLabs\BehatOpenApiExtension\Context;

use Behat\Gherkin\Node\PyStringNode;
use Behat\Gherkin\Node\TableNode;
use TwentytwoLabs\BehatOpenApiExtension\AsserterTrait;

/**
 * Class RestContext.
 */
class RestContext extends RawRestContext
{
    use AsserterTrait;

    /**
     * Add an header element in a request.
     *
     * @Then I add :name header equal to :value
     */
    public function iAddHeaderEqualTo($name, $value)
    {
        $client = $this->getClient();
        // Goutte\Client
        if (method_exists($client, 'setHeader')) {
            $client->setHeader($name, $value);
        } else {
            // Symfony\Component\BrowserKit\Client

            /* taken from Behat\Mink\Driver\BrowserKitDriver::setRequestHeader */
            $contentHeaders = ['CONTENT_LENGTH' => true, 'CONTENT_MD5' => true, 'CONTENT_TYPE' => true];
            $name = str_replace('-', '_', strtoupper($name));

            // CONTENT_* are not prefixed with HTTP_ in PHP when building $_SERVER
            if (!isset($contentHeaders[$name])) {
                $name = 'HTTP_'.$name;
            }
            /* taken from Behat\Mink\Driver\BrowserKitDriver::setRequestHeader */

            $client->setServerParameter($name, $value);
        }
    }

    /**
     * Sends a HTTP request.
     *
     * @Given I send a :method request to :path
     */
    public function iSendARequestTo($method, $path, PyStringNode $body = null)
    {
        $this->send($method, $this->locatePath($path), [], [], null !== $body ? $body->getRaw() : null);
    }

    /**
     * Sends a HTTP request with a some parameters.
     *
     * @Given I send a :method request to :path with parameters:
     */
    public function iSendARequestToWithParameters($method, $path, TableNode $data)
    {
        $parameters = [];

        foreach ($data->getHash() as $row) {
            if (!isset($row['key']) || !isset($row['value'])) {
                throw new \Exception("You must provide a 'key' and 'value' column in your table node.");
            }

            $parameters[$row['key']] = $row['value'];
        }

        $this->send($method, $this->locatePath($path), $parameters);
    }

    /**
     * Sends a HTTP request with a body.
     *
     * @Given I send a :method request to :path with body:
     */
    public function iSendARequestToWithBody($method, $path, PyStringNode $body)
    {
        $this->iSendARequestTo($method, $path, $body);
    }

    /**
     * Checks, that current page response status is equal to specified
     * Example: Then the response status code should be 200
     * Example: And the response status code should be 400.
     *
     * @Then /^the response status code should be equal to (?P<code>\d+)$/
     */
    public function assertResponseStatus($code)
    {
        $this->assertSession()->statusCodeEquals($code);
    }

    /**
     * Checks, whether the response content is equal to given text.
     *
     * @Then the response should be equal to
     * @Then the response should be equal to:
     */
    public function theResponseShouldBeEqualTo(PyStringNode $expected)
    {
        $expected = str_replace('\\"', '"', $expected);
        $actual = $this->getContent();

        $this->assertEquals(
            $expected,
            $actual,
            sprintf('Actual response is [%s], but expected [%s]', $actual, $expected)
        );
    }

    /**
     * Checks, whether the response content is null or empty string.
     *
     * @Then the response should be empty
     */
    public function theResponseShouldBeEmpty()
    {
        $actual = $this->getContent();

        $this->assertTrue(
            empty($actual),
            sprintf('The response of the current page is not empty, it is: %s', $actual)
        );
    }

    /**
     * Checks, whether the header name is equal to given text.
     *
     * @Then the header :name should be equal to :value
     */
    public function theHeaderShouldBeEqualTo($name, $value)
    {
        $actual = $this->getResponseHeader($name);

        $this->assertEquals(
            strtolower($value),
            strtolower($actual),
            sprintf('The header [%s] should be equal to [%s], but it is: [%s]', $name, $value, $actual)
        );
    }

    /**
     * Checks, whether the header name is not equal to given text.
     *
     * @Then the header :name should not be equal to :value
     */
    public function theHeaderShouldNotBeEqualTo($name, $value)
    {
        $actual = $this->getResponseHeader($name);

        if (strtolower($value) === strtolower($actual)) {
            throw new \Exception(sprintf('The header [%s] is equal to %s', $name, $actual));
        }
    }

    /**
     * Checks, whether the header name contains the given text.
     *
     * @Then the header :name should contain :value
     */
    public function theHeaderShouldContain($name, $value)
    {
        $actual = $this->getResponseHeader($name);

        $this->assertContains(
            $value,
            $actual,
            sprintf('The header [%s] should contain value [%s] but actual value is [%s]', $name, $value, $actual)
        );
    }

    /**
     * Checks, whether the header name doesn't contain the given text.
     *
     * @Then the header :name should not contain :value
     */
    public function theHeaderShouldNotContain($name, $value)
    {
        $this->assertNotContains(
            $value,
            $this->getResponseHeader($name),
            sprintf('The header [%s] contains [%s]', $name, $value)
        );
    }

    /**
     * Checks, whether the header not exist.
     *
     * @Then the header :name should not exist
     */
    public function theHeaderShouldNotExist($name)
    {
        $this->not(
            function () use ($name) {
                $this->theHeaderShouldExist($name);
            },
            sprintf('The header [%s] exists', $name)
        );
    }

    protected function theHeaderShouldExist($name): ?string
    {
        return $this->getResponseHeader($name);
    }

    /**
     * @Then the header :name should match :regex
     */
    public function theHeaderShouldMatch($name, $regex)
    {
        $actual = $this->getResponseHeader($name);

        $this->assertEquals(
            1,
            preg_match($regex, $actual),
            sprintf('The header [%s] should match [%s], but it is: [%s]', $name, $regex, $actual)
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
            sprintf('The header [%s] should not match [%s]', $name, $regex)
        );
    }

    /**
     * Checks, that the response header expire is in the future.
     *
     * @Then the response should expire in the future
     */
    public function theResponseShouldExpireInTheFuture()
    {
        $date = new \DateTime($this->getResponseHeader('Date'));
        $expires = new \DateTime($this->getResponseHeader('Expires'));

        $this->assertSame(
            1,
            $expires->diff($date)->invert,
            sprintf('The response doesn\'t expire in the future (%s)', $expires->format(DATE_ATOM))
        );
    }

    /**
     * @Then the response should be encoded in :encoding
     */
    public function theResponseShouldBeEncodedIn($encoding)
    {
        $content = $this->getContent();
        if (!mb_check_encoding($content, $encoding)) {
            throw new \Exception("The response is not encoded in $encoding");
        }

        $this->theHeaderShouldContain('Content-Type', sprintf('charset=%s', $encoding));
    }
}
