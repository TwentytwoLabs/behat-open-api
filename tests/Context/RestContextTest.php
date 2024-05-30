<?php

declare(strict_types=1);

namespace TwentytwoLabs\BehatOpenApiExtension\Tests\Context;

use Behat\Mink\Driver\BrowserKitDriver;
use Behat\Mink\Mink;
use Behat\Mink\Session;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\BrowserKit\HttpBrowser;
use Symfony\Component\DomCrawler\Crawler;
use TwentytwoLabs\BehatOpenApiExtension\Context\RestContext;
use PHPUnit\Framework\TestCase;

final class RestContextTest extends TestCase
{
    private Mink|MockObject $mink;

    protected function setUp(): void
    {
        $this->mink = $this->createMock(Mink::class);
    }

    #[DataProvider('getHeaders')]
    public function testShouldAddHeader(string $expected, string $name, string $value): void
    {
        $client = $this->createMock(HttpBrowser::class);
        $client->expects($this->once())->method('setServerParameter')->with($name, $value);

        $driver = $this->createMock(BrowserKitDriver::class);
        $driver->expects($this->once())->method('getClient')->willReturn($client);

        $session = $this->createMock(Session::class);
        $session->expects($this->once())->method('getDriver')->willReturn($driver);

        $this->mink->expects($this->once())->method('getSession')->with(null)->willReturn($session);

        $context = $this->getContext();
        $context->iAddHeaderEqualTo($expected, $value);
    }

    /**
     * @return array<int, array<int, string>>
     */
    public static function getHeaders(): array
    {
        return [
            [
                'x-uuid',
                'HTTP_X_UUID',
                '1234',
            ],
            [
                'CONTENT_LENGTH',
                'CONTENT_LENGTH',
                '1234',
            ],
            [
                'CONTENT_MD5',
                'CONTENT_MD5',
                '1234',
            ],
            [
                'CONTENT_TYPE',
                'CONTENT_TYPE',
                'application/json',
            ],
        ];
    }

    public function testShouldSendARequest(): void
    {
        $crawler = $this->createMock(Crawler::class);

        $followRedirectsMocker = $this->exactly(2);
        $client = $this->createMock(HttpBrowser::class);
        $client
            ->expects($followRedirectsMocker)
            ->method('followRedirects')
            ->willReturnCallback(function (bool $followRedirects) use ($followRedirectsMocker) {
                match ($followRedirectsMocker->numberOfInvocations()) {
                    1 => $this->assertFalse($followRedirects),
                    2 => $this->assertTrue($followRedirects),
                    default => throw new \Exception(sprintf('Method %s should be call %d times', 'followRedirects', 2)),
                };
            })
        ;
        $client
            ->expects($this->once())
            ->method('request')
            ->with('GET', 'http://example.org/foo', [], [], [], null)
            ->willReturn($crawler)
        ;
        $client->expects($this->once())->method('setServerParameters')->with([]);

        $driver = $this->createMock(BrowserKitDriver::class);
        $driver->expects($this->once())->method('getClient')->willReturn($client);

        $session = $this->createMock(Session::class);
        $session->expects($this->once())->method('getDriver')->willReturn($driver);

        $this->mink->expects($this->once())->method('getSession')->with(null)->willReturn($session);

        $context = $this->getContext();
        $context->setMinkParameters(['base_url' => 'http://example.org']);
        $context->iSendARequestTo('GET', '/foo');
    }

    public function testShouldSendARequestWithBody(): void
    {
        $crawler = $this->createMock(Crawler::class);

        $followRedirectsMocker = $this->exactly(2);
        $client = $this->createMock(HttpBrowser::class);
        $client
            ->expects($followRedirectsMocker)
            ->method('followRedirects')
            ->willReturnCallback(function (bool $followRedirects) use ($followRedirectsMocker) {
                match ($followRedirectsMocker->numberOfInvocations()) {
                    1 => $this->assertFalse($followRedirects),
                    2 => $this->assertTrue($followRedirects),
                    default => throw new \Exception(sprintf('Method %s should be call %d times', 'followRedirects', 2)),
                };
            })
        ;
        $client
            ->expects($this->once())
            ->method('request')
            ->with('GET', 'http://example.org/foo', [], [], [], null)
            ->willReturn($crawler)
        ;
        $client->expects($this->once())->method('setServerParameters')->with([]);

        $driver = $this->createMock(BrowserKitDriver::class);
        $driver->expects($this->once())->method('getClient')->willReturn($client);

        $session = $this->createMock(Session::class);
        $session->expects($this->once())->method('getDriver')->willReturn($driver);

        $this->mink->expects($this->once())->method('getSession')->with(null)->willReturn($session);

        $context = $this->getContext();
        $context->setMinkParameters(['base_url' => 'http://example.org']);
        $context->iSendARequestTo('GET', '/foo');
    }

    private function getContext(): RestContext
    {
        $context = new RestContext();
        $context->setMink($this->mink);

        return $context;
    }
}
