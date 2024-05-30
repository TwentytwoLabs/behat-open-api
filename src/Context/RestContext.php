<?php

declare(strict_types=1);

namespace TwentytwoLabs\BehatOpenApiExtension\Context;

use Behat\Gherkin\Node\PyStringNode;
use Behat\Gherkin\Node\TableNode;
use Webmozart\Assert\Assert;

final class RestContext extends RawRestContext
{
    /**
     * Add an header element in a request.
     *
     * @Then I add :name header equal to :value
     */
    public function iAddHeaderEqualTo(string $name, string $value): void
    {
        $client = $this->getClient();
        // Goutte\Client
        if (method_exists($client, 'setHeader')) {
            $client->setHeader($name, $value);
        } else {
            // Symfony\Component\BrowserKit\Client

            /* taken from Behat\Mink\Driver\BrowserKitDriver::setRequestHeader */
            $contentHeaders = ['CONTENT_LENGTH', 'CONTENT_MD5', 'CONTENT_TYPE'];
            $name = str_replace('-', '_', strtoupper($name));

            // CONTENT_* are not prefixed with HTTP_ in PHP when building $_SERVER
            if (!\in_array($name, $contentHeaders)) {
                $name = sprintf('HTTP_%s', $name);
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
    public function iSendARequestTo(string $method, string $path, ?PyStringNode $body = null): void
    {
        $this->send($method, $this->locatePath($path), [], [], $body?->getRaw());
    }

    /**
     * Sends a HTTP request with a some parameters.
     *
     * @Given I send a :method request to :path with parameters:
     */
    public function iSendARequestToWithParameters(string $method, string $path, TableNode $data): void
    {
        $parameters = [];
        $files = [];

        foreach ($data->getHash() as $row) {
            if (!isset($row['key']) || !isset($row['value'])) {
                throw new \Exception("You must provide a 'key' and 'value' column in your table node.");
            }

            if (is_string($row['value']) && str_starts_with($row['value'], '@')) {
                $filePath = rtrim($this->getMinkParameter('files_path'), DIRECTORY_SEPARATOR);
                $files[$row['key']] = sprintf('%s%s%s', $filePath, DIRECTORY_SEPARATOR, substr($row['value'], 1));
            } else {
                $parameters[$row['key']] = $row['value'];
            }
        }

        parse_str(http_build_query($parameters), $output);

        $this->send($method, $this->locatePath($path), $output, $files);
    }

    /**
     * Sends a HTTP request with a body.
     *
     * @Given I send a :method request to :path with body:
     */
    public function iSendARequestToWithBody(string $method, string $path, PyStringNode $body): void
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
    public function assertResponseStatus(int $code): void
    {
        $this->assertSession()->statusCodeEquals($code);
    }

    /**
     * Checks, whether the response content is equal to given text.
     *
     * @Then the response should be equal to
     * @Then the response should be equal to:
     */
    public function theResponseShouldBeEqualTo(PyStringNode $expected): void
    {
        $expected = str_replace('\\"', '"', $expected->getRaw());
        $actual = $this->getContent();

        Assert::same($actual, $expected, sprintf('Actual response is [%s], but expected [%s]', $actual, $expected));
    }

    /**
     * Checks, whether the response content is null or empty string.
     *
     * @Then the response should be empty
     */
    public function theResponseShouldBeEmpty(): void
    {
        $actual = $this->getContent();

        Assert::isEmpty($actual, sprintf('The response of the current page is not empty, it is: %s', $actual));
    }

    /**
     * Checks, whether the header name is equal to given text.
     *
     * @Then the header :name should be equal to :value
     */
    public function theHeaderShouldBeEqualTo(string $name, string $value): void
    {
        $actual = $this->getResponseHeader($name);

        Assert::same(
            null === $actual ? null : strtolower($actual),
            strtolower($value),
            sprintf('The header [%s] should be equal to [%s], but it is: [%s]', $name, $value, $actual)
        );
    }

    /**
     * Checks, whether the header name is not equal to given text.
     *
     * @Then the header :name should not be equal to :value
     */
    public function theHeaderShouldNotBeEqualTo(string $name, string $value): void
    {
        $actual = $this->getResponseHeader($name);

        Assert::notSame(
            strtolower($actual),
            strtolower($value),
            sprintf('The header [%s] is equal to %s', $name, $actual)
        );
    }

    /**
     * Checks, whether the header name contains the given text.
     *
     * @Then the header :name should contain :value
     */
    public function theHeaderShouldContain(string $name, string $value): void
    {
        $actual = $this->getResponseHeader($name);

        Assert::contains(
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
    public function theHeaderShouldNotContain(string $name, string $value): void
    {
        Assert::notContains(
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
    public function theHeaderShouldNotExist(string $name): void
    {
        Assert::null($this->getResponseHeader($name));
    }

    /**
     * @Then the header :name should match :regex
     */
    public function theHeaderShouldMatch(string $name, string $regex): void
    {
        $actual = $this->getResponseHeader($name);

        Assert::regex(
            $regex,
            $actual,
            sprintf('The header [%s] should match [%s], but it is: [%s]', $name, $regex, $actual)
        );
    }

    /**
     * @Then the header :name should not match :regex
     */
    public function theHeaderShouldNotMatch(string $name, string $regex): void
    {
        $actual = $this->getResponseHeader($name);

        Assert::notRegex(
            $regex,
            $actual,
            sprintf('The header [%s] should match [%s], but it is: [%s]', $name, $regex, $actual)
        );
    }

    /**
     * Checks, that the response header expire is in the future.
     *
     * @Then the response should expire in the future
     */
    public function theResponseShouldExpireInTheFuture(): void
    {
        $date = new \DateTime($this->getResponseHeader('Date'));
        $expires = new \DateTime($this->getResponseHeader('Expires'));

        Assert::same(
            $expires->diff($date)->invert,
            1,
            sprintf('The response doesn\'t expire in the future (%s)', $expires->format(DATE_ATOM))
        );
    }

    /**
     * @Then the response should be encoded in :encoding
     */
    public function theResponseShouldBeEncodedIn(string $encoding): void
    {
        $content = $this->getContent();
        if (!mb_check_encoding($content, $encoding)) {
            throw new \Exception("The response is not encoded in $encoding");
        }

        $this->theHeaderShouldContain('Content-Type', sprintf('charset=%s', $encoding));
    }

    protected function theHeaderShouldExist(string $name): ?string
    {
        return $this->getResponseHeader($name);
    }
}
