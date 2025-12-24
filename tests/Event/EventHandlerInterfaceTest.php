<?php

declare(strict_types=1);

namespace Breeze\Event;

use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;
use PHPUnit\Framework\TestCase;

#[AllowMockObjectsWithoutExpectations]
class EventHandlerInterfaceTest extends TestCase
{
	public function testIsInterface(): void
	{
		$reflection = new \ReflectionClass(EventHandlerInterface::class);
		$this->assertTrue($reflection->isInterface());
	}

	public function testHasResolveMethod(): void
	{
		$reflection = new \ReflectionClass(EventHandlerInterface::class);
		$this->assertTrue($reflection->hasMethod('resolve'));
	}

	public function testResolveMethodReturnsArray(): void
	{
		$reflection = new \ReflectionClass(EventHandlerInterface::class);
		$method = $reflection->getMethod('resolve');
		$returnType = $method->getReturnType();

		$this->assertNotNull($returnType);
		$this->assertEquals('array', $returnType->getName());
	}

	public function testResolveMethodHasNoParameters(): void
	{
		$reflection = new \ReflectionClass(EventHandlerInterface::class);
		$method = $reflection->getMethod('resolve');

		$this->assertCount(0, $method->getParameters());
	}

	public function testInterfaceHasOnlyOneMethod(): void
	{
		$reflection = new \ReflectionClass(EventHandlerInterface::class);
		$methods = $reflection->getMethods();

		$this->assertCount(1, $methods);
	}

	public function testCanBeImplemented(): void
	{
		$mock = $this->createStub(EventHandlerInterface::class);
		$this->assertInstanceOf(EventHandlerInterface::class, $mock);
	}

	public function testResolveMethodCanBeMocked(): void
	{
		$mock = $this->createMock(EventHandlerInterface::class);
		$expectedResult = ['key' => 'value'];

		$mock->expects($this->once())
			->method('resolve')
			->willReturn($expectedResult);

		$result = $mock->resolve();
		$this->assertEquals($expectedResult, $result);
	}
}
