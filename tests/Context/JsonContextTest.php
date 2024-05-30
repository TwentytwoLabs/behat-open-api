<?php

declare(strict_types=1);

namespace TwentytwoLabs\BehatOpenApiExtension\Tests\Context;

use Behat\Gherkin\Node\PyStringNode;
use Behat\Gherkin\Node\TableNode;
use Behat\Mink\Driver\DriverInterface;
use Behat\Mink\Mink;
use Behat\Mink\Session;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use TwentytwoLabs\ArrayComparator\Comparator\ComparatorChain;
use TwentytwoLabs\BehatOpenApiExtension\Context\JsonContext;
use TwentytwoLabs\BehatOpenApiExtension\Exception\ArrayContainsComparatorException;
use Webmozart\Assert\InvalidArgumentException;

final class JsonContextTest extends TestCase
{
    private Mink|MockObject $mink;

    protected function setUp(): void
    {
        $this->mink = $this->createMock(Mink::class);
    }

    public function testShouldNotValidateJsonResponseBecauseMissingContentType(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('The value "" does not match the expected pattern.');

        $session = $this->createMock(Session::class);
        $session
            ->expects($this->once())
            ->method('getResponseHeader')
            ->with('Content-Type')
            ->willReturn('')
        ;
        $session->expects($this->never())->method('getDriver');

        $this->mink->expects($this->once())->method('getSession')->willReturn($session);

        $context = $this->getContext();
        $context->theResponseShouldBeInJson();
    }

    public function testShouldNotValidateJsonResponseBecauseContentIsEmpty(): void
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage("The string '' is not valid json");

        $driver = $this->createMock(DriverInterface::class);
        $driver->expects($this->once())->method('getContent')->willReturn('');

        $session = $this->createMock(Session::class);
        $session
            ->expects($this->once())
            ->method('getResponseHeader')
            ->with('Content-Type')
            ->willReturn('application/json; charset=utf-8')
        ;
        $session->expects($this->once())->method('getDriver')->willReturn($driver);

        $this->mink->expects($this->exactly(2))->method('getSession')->willReturn($session);

        $context = $this->getContext();
        $context->theResponseShouldBeInJson();
    }

    public function testShouldValidateJsonResponse(): void
    {
        $driver = $this->createMock(DriverInterface::class);
        $driver->expects($this->once())->method('getContent')->willReturn('{"foo": "bar"}');

        $session = $this->createMock(Session::class);
        $session
            ->expects($this->once())
            ->method('getResponseHeader')
            ->with('Content-Type')
            ->willReturn('application/json; charset=utf-8')
        ;
        $session->expects($this->once())->method('getDriver')->willReturn($driver);

        $this->mink->expects($this->exactly(2))->method('getSession')->willReturn($session);

        $context = $this->getContext();
        $context->theResponseShouldBeInJson();
    }

    public function testShouldNotValidateResponseIsNotInJsonBecauseContentTypeIsForJson(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('The response is in JSON');

        $session = $this->createMock(Session::class);
        $session
            ->expects($this->once())
            ->method('getResponseHeader')
            ->with('Content-Type')
            ->willReturn('application/json; charset=utf-8')
        ;
        $session->expects($this->never())->method('getDriver');

        $this->mink->expects($this->once())->method('getSession')->willReturn($session);

        $context = $this->getContext();
        $context->theResponseShouldNotBeInJson();
    }

    public function testShouldNotValidateResponseIsNotInJsonBecauseContentIsForJson(): void
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('The response is in JSON');

        $driver = $this->createMock(DriverInterface::class);
        $driver->expects($this->once())->method('getContent')->willReturn('{"foo": "bar"}');

        $session = $this->createMock(Session::class);
        $session
            ->expects($this->once())
            ->method('getResponseHeader')
            ->with('Content-Type')
            ->willReturn('application/xml; charset=utf-8')
        ;
        $session->expects($this->once())->method('getDriver')->willReturn($driver);

        $this->mink->expects($this->exactly(2))->method('getSession')->willReturn($session);

        $context = $this->getContext();
        $context->theResponseShouldNotBeInJson();
    }

    public function testShouldValidateResponseIsNotInJson(): void
    {
        $driver = $this->createMock(DriverInterface::class);
        $driver->expects($this->once())->method('getContent')->willReturn('<note><to>Tove</to><from>Jani</from></note>');

        $session = $this->createMock(Session::class);
        $session
            ->expects($this->once())
            ->method('getResponseHeader')
            ->with('Content-Type')
            ->willReturn('application/xml; charset=utf-8')
        ;
        $session->expects($this->once())->method('getDriver')->willReturn($driver);

        $this->mink->expects($this->exactly(2))->method('getSession')->willReturn($session);

        $context = $this->getContext();
        $context->theResponseShouldNotBeInJson();
    }

    public function testShouldNotValidateValueOfNodeBecauseValueNotCorresponding(): void
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('The node value is \'"bar"\'');

        $driver = $this->createMock(DriverInterface::class);
        $driver->expects($this->once())->method('getContent')->willReturn('{"foo": "bar","bar":"baz"}');

        $session = $this->createMock(Session::class);
        $session->expects($this->once())->method('getDriver')->willReturn($driver);

        $this->mink->expects($this->once())->method('getSession')->willReturn($session);

        $context = $this->getContext();
        $context->theJsonNodeShouldBeEqualTo('foo', 'baz');
    }

    public function testShouldValidateValueOfNode(): void
    {
        $driver = $this->createMock(DriverInterface::class);
        $driver->expects($this->once())->method('getContent')->willReturn('[{"foo": "bar"},{"bar":"baz"}]');

        $session = $this->createMock(Session::class);
        $session->expects($this->once())->method('getDriver')->willReturn($driver);

        $this->mink->expects($this->once())->method('getSession')->willReturn($session);

        $context = $this->getContext();
        $context->theJsonNodeShouldBeEqualTo('root->[0]->foo ', 'bar');
    }

    public function testShouldNotValidateValueOfNodes(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('The node value is \'"baz"\'');

        $driver = $this->createMock(DriverInterface::class);
        $driver->expects($this->once())->method('getContent')->willReturn('{"foo": "baz"}');

        $session = $this->createMock(Session::class);
        $session->expects($this->once())->method('getDriver')->willReturn($driver);

        $this->mink->expects($this->once())->method('getSession')->willReturn($session);

        $nodes = $this->createMock(TableNode::class);
        $nodes->expects($this->once())->method('getRowsHash')->willReturn(['foo' => 'bar']);

        $context = $this->getContext();
        $context->theJsonNodesShouldBeEqualTo($nodes);
    }

    public function testShouldValidateValueOfNodes(): void
    {
        $driver = $this->createMock(DriverInterface::class);
        $driver->expects($this->once())->method('getContent')->willReturn('{"foo": "bar"}');

        $session = $this->createMock(Session::class);
        $session->expects($this->once())->method('getDriver')->willReturn($driver);

        $this->mink->expects($this->once())->method('getSession')->willReturn($session);

        $nodes = $this->createMock(TableNode::class);
        $nodes->expects($this->once())->method('getRowsHash')->willReturn(['foo' => 'bar']);

        $context = $this->getContext();
        $context->theJsonNodesShouldBeEqualTo($nodes);
    }

    public function testShouldNotValidateRegexOfNode(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('The node value is \'"Lorem Ipsum"\'');

        $driver = $this->createMock(DriverInterface::class);
        $driver->expects($this->once())->method('getContent')->willReturn('{"foo": "Lorem Ipsum"}');

        $session = $this->createMock(Session::class);
        $session->expects($this->once())->method('getDriver')->willReturn($driver);

        $this->mink->expects($this->once())->method('getSession')->willReturn($session);

        $context = $this->getContext();
        $context->theJsonNodeShouldMatch('foo', '#ip#');
    }

    public function testShouldValidateRegexOfNode(): void
    {
        $driver = $this->createMock(DriverInterface::class);
        $driver->expects($this->once())->method('getContent')->willReturn('{"foo": "Lorem Ipsum"}');

        $session = $this->createMock(Session::class);
        $session->expects($this->once())->method('getDriver')->willReturn($driver);

        $this->mink->expects($this->once())->method('getSession')->willReturn($session);

        $context = $this->getContext();
        $context->theJsonNodeShouldMatch('foo', '#Ip#');
    }

    public function testShouldNotValidateOfNullNode(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('The node value is `"Lorem Ipsum"`');

        $driver = $this->createMock(DriverInterface::class);
        $driver->expects($this->once())->method('getContent')->willReturn('{"foo": "Lorem Ipsum"}');

        $session = $this->createMock(Session::class);
        $session->expects($this->once())->method('getDriver')->willReturn($driver);

        $this->mink->expects($this->once())->method('getSession')->willReturn($session);

        $context = $this->getContext();
        $context->theJsonNodeShouldBeNull('foo');
    }

    public function testShouldValidateOfNullNode(): void
    {
        $driver = $this->createMock(DriverInterface::class);
        $driver->expects($this->once())->method('getContent')->willReturn('{"foo": null}');

        $session = $this->createMock(Session::class);
        $session->expects($this->once())->method('getDriver')->willReturn($driver);

        $this->mink->expects($this->once())->method('getSession')->willReturn($session);

        $context = $this->getContext();
        $context->theJsonNodeShouldBeNull('foo');
    }

    public function testShouldNotValidateOfNotNullNode(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('The node null should not be null');

        $driver = $this->createMock(DriverInterface::class);
        $driver->expects($this->once())->method('getContent')->willReturn('{"foo": null}');

        $session = $this->createMock(Session::class);
        $session->expects($this->once())->method('getDriver')->willReturn($driver);

        $this->mink->expects($this->once())->method('getSession')->willReturn($session);

        $context = $this->getContext();
        $context->theJsonNodeShouldNotBeNull('foo');
    }

    public function testShouldValidateOfNotNullNode(): void
    {
        $driver = $this->createMock(DriverInterface::class);
        $driver->expects($this->once())->method('getContent')->willReturn('{"foo": "Lorem Ipsum"}');

        $session = $this->createMock(Session::class);
        $session->expects($this->once())->method('getDriver')->willReturn($driver);

        $this->mink->expects($this->once())->method('getSession')->willReturn($session);

        $context = $this->getContext();
        $context->theJsonNodeShouldNotBeNull('foo');
    }

    public function testShouldNotValidateNodeWithTrueValue(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('The node value is `"Lorem Ipsum"`');

        $driver = $this->createMock(DriverInterface::class);
        $driver->expects($this->once())->method('getContent')->willReturn('{"foo": "Lorem Ipsum"}');

        $session = $this->createMock(Session::class);
        $session->expects($this->once())->method('getDriver')->willReturn($driver);

        $this->mink->expects($this->once())->method('getSession')->willReturn($session);

        $context = $this->getContext();
        $context->theJsonNodeShouldBeTrue('foo');
    }

    public function testShouldValidateNodeWithTrueValue(): void
    {
        $driver = $this->createMock(DriverInterface::class);
        $driver->expects($this->once())->method('getContent')->willReturn('{"foo": true}');

        $session = $this->createMock(Session::class);
        $session->expects($this->once())->method('getDriver')->willReturn($driver);

        $this->mink->expects($this->once())->method('getSession')->willReturn($session);

        $context = $this->getContext();
        $context->theJsonNodeShouldBeTrue('foo');
    }

    public function testShouldNotValidateNodeWithFalseValue(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('The node value is `"Lorem Ipsum"`');

        $driver = $this->createMock(DriverInterface::class);
        $driver->expects($this->once())->method('getContent')->willReturn('{"foo": "Lorem Ipsum"}');

        $session = $this->createMock(Session::class);
        $session->expects($this->once())->method('getDriver')->willReturn($driver);

        $this->mink->expects($this->once())->method('getSession')->willReturn($session);

        $context = $this->getContext();
        $context->theJsonNodeShouldBeFalse('foo');
    }

    public function testShouldValidateNodeWithFalseValue(): void
    {
        $driver = $this->createMock(DriverInterface::class);
        $driver->expects($this->once())->method('getContent')->willReturn('{"foo": false}');

        $session = $this->createMock(Session::class);
        $session->expects($this->once())->method('getDriver')->willReturn($driver);

        $this->mink->expects($this->once())->method('getSession')->willReturn($session);

        $context = $this->getContext();
        $context->theJsonNodeShouldBeFalse('foo');
    }

    public function testShouldNotValidateNodeWithNumberValue(): void
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('The node value is `"Lorem Ipsum"`');

        $driver = $this->createMock(DriverInterface::class);
        $driver->expects($this->once())->method('getContent')->willReturn('{"foo": "Lorem Ipsum"}');

        $session = $this->createMock(Session::class);
        $session->expects($this->once())->method('getDriver')->willReturn($driver);

        $this->mink->expects($this->once())->method('getSession')->willReturn($session);

        $context = $this->getContext();
        $context->theJsonNodeShouldBeEqualToTheNumber('foo', '42');
    }

    public function testShouldValidateNodeWithNumberValue(): void
    {
        $driver = $this->createMock(DriverInterface::class);
        $driver->expects($this->once())->method('getContent')->willReturn('{"foo": 42}');

        $session = $this->createMock(Session::class);
        $session->expects($this->once())->method('getDriver')->willReturn($driver);

        $this->mink->expects($this->once())->method('getSession')->willReturn($session);

        $context = $this->getContext();
        $context->theJsonNodeShouldBeEqualToTheNumber('foo', '42');
    }

    public function testShouldValidateNodeWithNumberValue2(): void
    {
        $driver = $this->createMock(DriverInterface::class);
        $driver->expects($this->once())->method('getContent')->willReturn('{"foo": 42.0}');

        $session = $this->createMock(Session::class);
        $session->expects($this->once())->method('getDriver')->willReturn($driver);

        $this->mink->expects($this->once())->method('getSession')->willReturn($session);

        $context = $this->getContext();
        $context->theJsonNodeShouldBeEqualToTheNumber('foo', '42');
    }

    public function testShouldValidateNodeWithNumberValue3(): void
    {
        $driver = $this->createMock(DriverInterface::class);
        $driver->expects($this->once())->method('getContent')->willReturn('{"foo": 42}');

        $session = $this->createMock(Session::class);
        $session->expects($this->once())->method('getDriver')->willReturn($driver);

        $this->mink->expects($this->once())->method('getSession')->willReturn($session);

        $context = $this->getContext();
        $context->theJsonNodeShouldBeEqualToTheNumber('foo', '42.0');
    }

    public function testShouldNotValidateNodeWithChildren(): void
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Expected an array to contain 3 elements. Got: 2.');

        $driver = $this->createMock(DriverInterface::class);
        $driver->expects($this->once())->method('getContent')->willReturn('{"foo": [42, 50]}');

        $session = $this->createMock(Session::class);
        $session->expects($this->once())->method('getDriver')->willReturn($driver);

        $this->mink->expects($this->once())->method('getSession')->willReturn($session);

        $context = $this->getContext();
        $context->theJsonNodeShouldHaveElements('foo', 3);
    }

    public function testShouldValidateNodeWithChildren(): void
    {
        $driver = $this->createMock(DriverInterface::class);
        $driver->expects($this->once())->method('getContent')->willReturn('{"foo": [42, 50]}');

        $session = $this->createMock(Session::class);
        $session->expects($this->once())->method('getDriver')->willReturn($driver);

        $this->mink->expects($this->once())->method('getSession')->willReturn($session);

        $context = $this->getContext();
        $context->theJsonNodeShouldHaveElements('foo', 2);
    }

    public function testShouldValidateNodeWithChildren2(): void
    {
        $driver = $this->createMock(DriverInterface::class);
        $driver->expects($this->once())->method('getContent')->willReturn('{"foo": 42}');

        $session = $this->createMock(Session::class);
        $session->expects($this->once())->method('getDriver')->willReturn($driver);

        $this->mink->expects($this->once())->method('getSession')->willReturn($session);

        $context = $this->getContext();
        $context->theJsonNodeShouldHaveElements('foo', 1);
    }

    public function testShouldNotValidateNodeWithContains(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('The value "Lorem Ipsum" does not match the expected pattern.');

        $driver = $this->createMock(DriverInterface::class);
        $driver->expects($this->once())->method('getContent')->willReturn('{"foo": "Lorem Ipsum"}');

        $session = $this->createMock(Session::class);
        $session->expects($this->once())->method('getDriver')->willReturn($driver);

        $this->mink->expects($this->once())->method('getSession')->willReturn($session);

        $context = $this->getContext();
        $context->theJsonNodeShouldContain('foo', '^application/json');
    }

    public function testShouldValidateNodeWithContains(): void
    {
        $driver = $this->createMock(DriverInterface::class);
        $driver->expects($this->once())->method('getContent')->willReturn('{"foo": "Lorem Ipsum"}');

        $session = $this->createMock(Session::class);
        $session->expects($this->once())->method('getDriver')->willReturn($driver);

        $this->mink->expects($this->once())->method('getSession')->willReturn($session);

        $context = $this->getContext();
        $context->theJsonNodeShouldContain('foo', 'Ip');
    }

    public function testShouldNotValidateNodesWithContains(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('The value "Lorem Ipsum" does not match the expected pattern.');

        $driver = $this->createMock(DriverInterface::class);
        $driver->expects($this->once())->method('getContent')->willReturn('{"foo": "Lorem Ipsum"}');

        $session = $this->createMock(Session::class);
        $session->expects($this->once())->method('getDriver')->willReturn($driver);

        $this->mink->expects($this->once())->method('getSession')->willReturn($session);

        $table = $this->createMock(TableNode::class);
        $table->expects($this->once())->method('getRowsHash')->willReturn(['foo' => '^bar']);

        $context = $this->getContext();
        $context->theJsonNodesShouldContain($table);
    }

    public function testShouldValidateNodesWithContains(): void
    {
        $driver = $this->createMock(DriverInterface::class);
        $driver->expects($this->once())->method('getContent')->willReturn('{"foo": "Lorem Ipsum"}');

        $session = $this->createMock(Session::class);
        $session->expects($this->once())->method('getDriver')->willReturn($driver);

        $this->mink->expects($this->once())->method('getSession')->willReturn($session);

        $table = $this->createMock(TableNode::class);
        $table->expects($this->once())->method('getRowsHash')->willReturn(['foo' => 'Ip']);

        $context = $this->getContext();
        $context->theJsonNodesShouldContain($table);
    }

    public function testShouldNotValidateNodeWithNoContains(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('"Lorem" was not expected to be contained in a value. Got: "Lorem Ipsum"');

        $driver = $this->createMock(DriverInterface::class);
        $driver->expects($this->once())->method('getContent')->willReturn('{"foo": "Lorem Ipsum"}');

        $session = $this->createMock(Session::class);
        $session->expects($this->once())->method('getDriver')->willReturn($driver);

        $this->mink->expects($this->once())->method('getSession')->willReturn($session);

        $context = $this->getContext();
        $context->theJsonNodeShouldNotContain('foo', 'Lorem');
    }

    public function testShouldValidateNodeWithNoContains(): void
    {
        $driver = $this->createMock(DriverInterface::class);
        $driver->expects($this->once())->method('getContent')->willReturn('{"foo": "Lorem Ipsum"}');

        $session = $this->createMock(Session::class);
        $session->expects($this->once())->method('getDriver')->willReturn($driver);

        $this->mink->expects($this->once())->method('getSession')->willReturn($session);

        $context = $this->getContext();
        $context->theJsonNodeShouldNotContain('foo', '^application/json');
    }

    public function testShouldNotValidateNodesWithNoContains(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('"Ip" was not expected to be contained in a value. Got: "Lorem Ipsum"');

        $driver = $this->createMock(DriverInterface::class);
        $driver->expects($this->once())->method('getContent')->willReturn('{"foo": "Lorem Ipsum"}');

        $session = $this->createMock(Session::class);
        $session->expects($this->once())->method('getDriver')->willReturn($driver);

        $this->mink->expects($this->once())->method('getSession')->willReturn($session);

        $table = $this->createMock(TableNode::class);
        $table->expects($this->once())->method('getRowsHash')->willReturn(['foo' => 'Ip']);

        $context = $this->getContext();
        $context->theJsonNodesShouldNotContain($table);
    }

    public function testShouldValidateNodesWithNoContains(): void
    {
        $driver = $this->createMock(DriverInterface::class);
        $driver->expects($this->once())->method('getContent')->willReturn('{"foo": "Lorem Ipsum"}');

        $session = $this->createMock(Session::class);
        $session->expects($this->once())->method('getDriver')->willReturn($driver);

        $this->mink->expects($this->once())->method('getSession')->willReturn($session);

        $table = $this->createMock(TableNode::class);
        $table->expects($this->once())->method('getRowsHash')->willReturn(['foo' => 'bar']);

        $context = $this->getContext();
        $context->theJsonNodesShouldNotContain($table);
    }

    public function testShouldNotValidateNodeExistBecauseItIsNotJson(): void
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('The node \'bar\' does not exist.');

        $driver = $this->createMock(DriverInterface::class);
        $driver->expects($this->once())->method('getContent')->willReturn('<note><to>Tove</to><from>Jani</from></note>');

        $session = $this->createMock(Session::class);
        $session->expects($this->once())->method('getDriver')->willReturn($driver);

        $this->mink->expects($this->once())->method('getSession')->willReturn($session);

        $context = $this->getContext();
        $context->theJsonNodeShouldExist('bar');
    }

    public function testShouldNotValidateNodeExist(): void
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('The node \'bar\' does not exist.');

        $driver = $this->createMock(DriverInterface::class);
        $driver->expects($this->once())->method('getContent')->willReturn('{"foo": "Lorem Ipsum"}');

        $session = $this->createMock(Session::class);
        $session->expects($this->once())->method('getDriver')->willReturn($driver);

        $this->mink->expects($this->once())->method('getSession')->willReturn($session);

        $context = $this->getContext();
        $context->theJsonNodeShouldExist('bar');
    }

    public function testShouldValidateNodeExist(): void
    {
        $driver = $this->createMock(DriverInterface::class);
        $driver->expects($this->once())->method('getContent')->willReturn('{"foo": "Lorem Ipsum"}');

        $session = $this->createMock(Session::class);
        $session->expects($this->once())->method('getDriver')->willReturn($driver);

        $this->mink->expects($this->once())->method('getSession')->willReturn($session);

        $context = $this->getContext();
        $context->theJsonNodeShouldExist('foo');
    }

    public function testShouldNotValidateNodeNotExist(): void
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('The node \'foo\' exists.');

        $driver = $this->createMock(DriverInterface::class);
        $driver->expects($this->once())->method('getContent')->willReturn('{"foo": "Lorem Ipsum"}');

        $session = $this->createMock(Session::class);
        $session->expects($this->once())->method('getDriver')->willReturn($driver);

        $this->mink->expects($this->once())->method('getSession')->willReturn($session);

        $context = $this->getContext();
        $context->theJsonNodeShouldNotExist('foo');
    }

    public function testShouldValidateNodeNotExistBecauseItIsNotJson(): void
    {
        $driver = $this->createMock(DriverInterface::class);
        $driver->expects($this->once())->method('getContent')->willReturn('<note><to>Tove</to><from>Jani</from></note>');

        $session = $this->createMock(Session::class);
        $session->expects($this->once())->method('getDriver')->willReturn($driver);

        $this->mink->expects($this->once())->method('getSession')->willReturn($session);

        $context = $this->getContext();
        $context->theJsonNodeShouldNotExist('foo');
    }

    public function testShouldValidateNodeNotExist(): void
    {
        $driver = $this->createMock(DriverInterface::class);
        $driver->expects($this->once())->method('getContent')->willReturn('{"foo": "Lorem Ipsum"}');

        $session = $this->createMock(Session::class);
        $session->expects($this->once())->method('getDriver')->willReturn($driver);

        $this->mink->expects($this->once())->method('getSession')->willReturn($session);

        $context = $this->getContext();
        $context->theJsonNodeShouldNotExist('bar');
    }

    public function testShouldNotValidateCorrespondingOfNodesBecauseThereIsAnError(): void
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('The expected JSON is not a valid');

        $driver = $this->createMock(DriverInterface::class);
        $driver->expects($this->once())->method('getContent')->willReturn('{"bar": "Lorem Ipsum"}');

        $session = $this->createMock(Session::class);
        $session->expects($this->once())->method('getDriver')->willReturn($driver);

        $this->mink->expects($this->once())->method('getSession')->willReturn($session);

        $content = $this->createMock(PyStringNode::class);
        $content->expects($this->once())->method('__toString')->willThrowException(new \Exception('Lorem Ipsum'));

        $context = $this->getContext();
        $context->theJsonShouldBeEqualTo($content);
    }

    public function testShouldNotValidateCorrespondingOfNodes(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("The json is equal to:\n{\n    \"bar\": \"Lorem Ipsum\"\n}");

        $driver = $this->createMock(DriverInterface::class);
        $driver->expects($this->once())->method('getContent')->willReturn('{"bar": "Lorem Ipsum"}');

        $session = $this->createMock(Session::class);
        $session->expects($this->once())->method('getDriver')->willReturn($driver);

        $this->mink->expects($this->once())->method('getSession')->willReturn($session);

        $content = $this->createMock(PyStringNode::class);
        $content->expects($this->once())->method('__toString')->willReturn('{"foo": "Lorem Ipsum"}');

        $context = $this->getContext();
        $context->theJsonShouldBeEqualTo($content);
    }

    public function testShouldValidateCorresponding(): void
    {
        $driver = $this->createMock(DriverInterface::class);
        $driver->expects($this->once())->method('getContent')->willReturn('{"foo": "Lorem Ipsum"}');

        $session = $this->createMock(Session::class);
        $session->expects($this->once())->method('getDriver')->willReturn($driver);

        $this->mink->expects($this->once())->method('getSession')->willReturn($session);

        $content = $this->createMock(PyStringNode::class);
        $content->expects($this->once())->method('__toString')->willReturn('{"foo": "Lorem Ipsum"}');

        $context = $this->getContext();
        $context->theJsonShouldBeEqualTo($content);
    }

    public function testShouldNotValidateListCollectionBecauseMissingField(): void
    {
        $message = 'Keys [bar] are missing ' . PHP_EOL . PHP_EOL;
        $message .= "=============================================================================" . PHP_EOL;
        $message .= "= Needle ====================================================================" . PHP_EOL;
        $message .= "=============================================================================" . PHP_EOL;
        $message .= json_encode(['foo'], JSON_PRETTY_PRINT) . PHP_EOL;
        $message .= "=============================================================================" . PHP_EOL;
        $message .= "= Haystack ==================================================================" . PHP_EOL;
        $message .= "=============================================================================" . PHP_EOL;
        $message .= json_encode(['foo', 'bar'], JSON_PRETTY_PRINT) . PHP_EOL;

        $this->expectException(ArrayContainsComparatorException::class);
        $this->expectExceptionMessage($message);
        $this->expectExceptionCode(0);

        $driver = $this->createMock(DriverInterface::class);
        $driver->expects($this->once())->method('getContent')->willReturn('[{"foo": "Lorem Ipsum","bar":"baz"}]');

        $session = $this->createMock(Session::class);
        $session->expects($this->once())->method('getDriver')->willReturn($driver);

        $this->mink->expects($this->once())->method('getSession')->willReturn($session);

        $content = $this->createMock(TableNode::class);
        $content->expects($this->once())->method('getRows')->willReturn([['foo']]);

        $context = $this->getContext();
        $context->assertTableColumns('', $content);
    }

    public function testShouldNotValidateListCollectionBecauseFieldMustNotPresent(): void
    {
        $message = 'Keys [bar] must not be present ' . PHP_EOL . PHP_EOL;
        $message .= "=============================================================================" . PHP_EOL;
        $message .= "= Needle ====================================================================" . PHP_EOL;
        $message .= "=============================================================================" . PHP_EOL;
        $message .= json_encode(['foo', 'bar'], JSON_PRETTY_PRINT) . PHP_EOL;
        $message .= "=============================================================================" . PHP_EOL;
        $message .= "= Haystack ==================================================================" . PHP_EOL;
        $message .= "=============================================================================" . PHP_EOL;
        $message .= json_encode(['foo'], JSON_PRETTY_PRINT) . PHP_EOL;

        $this->expectException(ArrayContainsComparatorException::class);
        $this->expectExceptionMessage($message);
        $this->expectExceptionCode(0);

        $driver = $this->createMock(DriverInterface::class);
        $driver->expects($this->once())->method('getContent')->willReturn('[{"foo": "Lorem Ipsum"}]');

        $session = $this->createMock(Session::class);
        $session->expects($this->once())->method('getDriver')->willReturn($driver);

        $this->mink->expects($this->once())->method('getSession')->willReturn($session);

        $content = $this->createMock(TableNode::class);
        $content->expects($this->once())->method('getRows')->willReturn([['foo', 'bar']]);

        $context = $this->getContext();
        $context->assertTableColumns('', $content);
    }

    public function testShouldValidateListCollection(): void
    {
        $driver = $this->createMock(DriverInterface::class);
        $driver->expects($this->once())->method('getContent')->willReturn('[{"foo": "Lorem Ipsum"}]');

        $session = $this->createMock(Session::class);
        $session->expects($this->once())->method('getDriver')->willReturn($driver);

        $this->mink->expects($this->once())->method('getSession')->willReturn($session);

        $content = $this->createMock(TableNode::class);
        $content->expects($this->once())->method('getRows')->willReturn([['foo']]);

        $context = $this->getContext();
        $context->assertTableColumns('', $content);
    }

    public function testShouldNotMatching(): void
    {
        $driver = $this->createMock(DriverInterface::class);
        $driver->expects($this->once())->method('getContent')->willReturn('[{"foo": "Lorem Ipsum"}]');

        $session = $this->createMock(Session::class);
        $session->expects($this->once())->method('getDriver')->willReturn($driver);

        $this->mink->expects($this->once())->method('getSession')->willReturn($session);

        $content = $this->createMock(PyStringNode::class);
        $content->expects($this->once())->method('getRaw')->willReturn('[{"foo": "bar"}]');

        $comparatorChain = $this->createMock(ComparatorChain::class);
        $comparatorChain->expects($this->once())->method('compare')->with('bar', 'Lorem Ipsum');

        $context = $this->getContext();
        $context->setComparatorChain($comparatorChain);
        $context->theJsonShouldBeMatchTo($content);
    }

    public function testShouldNotMatchingBecauseThereIsSomeError(): void
    {
        $this->expectException(ArrayContainsComparatorException::class);
        $this->expectExceptionMessage('Foo');

        $driver = $this->createMock(DriverInterface::class);
        $driver->expects($this->once())->method('getContent')->willReturn('[{"foo": "Lorem Ipsum"}]');

        $session = $this->createMock(Session::class);
        $session->expects($this->once())->method('getDriver')->willReturn($driver);

        $this->mink->expects($this->once())->method('getSession')->willReturn($session);

        $content = $this->createMock(PyStringNode::class);
        $content->expects($this->once())->method('getRaw')->willReturn('[{"foo": "Lorem Ipsum"}]');

        $comparatorChain = $this->createMock(ComparatorChain::class);
        $comparatorChain->expects($this->once())->method('compare')->willThrowException(new \Exception('Foo'));

        $context = $this->getContext();
        $context->setComparatorChain($comparatorChain);
        $context->theJsonShouldBeMatchTo($content);
    }

    public function testShouldMatching(): void
    {
        $driver = $this->createMock(DriverInterface::class);
        $driver->expects($this->once())->method('getContent')->willReturn('[{"foo": "Lorem Ipsum"}]');

        $session = $this->createMock(Session::class);
        $session->expects($this->once())->method('getDriver')->willReturn($driver);

        $this->mink->expects($this->once())->method('getSession')->willReturn($session);

        $content = $this->createMock(PyStringNode::class);
        $content->expects($this->once())->method('getRaw')->willReturn('[{"foo": "Lorem Ipsum"}]');

        $comparatorChain = $this->createMock(ComparatorChain::class);
        $comparatorChain->expects($this->once())->method('compare')->with('Lorem Ipsum', 'Lorem Ipsum');

        $context = $this->getContext();
        $context->setComparatorChain($comparatorChain);
        $context->theJsonShouldBeMatchTo($content);
    }

    private function getContext(): JsonContext
    {
        $context = new JsonContext();
        $context->setMink($this->mink);

        return $context;
    }
}
