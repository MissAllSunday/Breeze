<?php

declare(strict_types=1);

namespace Breeze\Event\Buddy;

use Breeze\Entity\AlertEntity;
use Breeze\Event\EventHandlerInterface;
use Breeze\Repository\AlertRepository;
use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

#[AllowMockObjectsWithoutExpectations]
class BuddyAcceptedHandlerTest extends TestCase
{
	private AlertEntity|MockObject $alertEntity;

	private AlertRepository|MockObject $alertRepository;

	private BuddyAcceptedHandler|MockObject $handler;

	/**
	 * @throws Exception
	 */
	protected function setUp(): void
	{
		$this->alertEntity = $this->createMock(AlertEntity::class);
		$this->alertRepository = $this->createMock(AlertRepository::class);

		$this->handler = $this->getMockBuilder(BuddyAcceptedHandler::class)
			->setConstructorArgs([$this->alertEntity, $this->alertRepository])
			->onlyMethods(['getText', 'parserText', 'global'])
			->getMock();
	}

	public function testImplementsEventHandlerInterface(): void
	{
		$this->assertInstanceOf(EventHandlerInterface::class, $this->handler);
	}

	public function testResolveReturnsArray(): void
	{
		$expectedArray = ['key' => 'value'];

		$this->alertEntity->method('toArray')->willReturn($expectedArray);
		$this->handler->method('global')->willReturn('http://example.com');
		$this->handler->method('parserText')->willReturn('parsed text');

		$result = $this->handler->resolve();

		$this->assertIsArray($result);
		$this->assertEquals($expectedArray, $result);
	}

	public function testResolveSetsIcon(): void
	{
		$this->alertEntity->method('toArray')->willReturn([]);
		$this->handler->method('global')->willReturn('http://example.com');
		$this->handler->method('parserText')->willReturn('parsed text');

		$this->alertEntity->expects($this->once())
			->method('setIcon')
			->with($this->stringContains('alert_icon'));

		$this->handler->resolve();
	}

	public function testBuildAlertText(): void
	{
		$this->alertEntity->method('getSenderName')->willReturn('John Doe');
		$this->handler->expects($this->once())->method('getText')->with('alert_buddy_confirmed')->willReturn('Confirmed text');
		$this->handler->expects($this->once())->method('parserText')->willReturn('Parsed text');
		$this->alertEntity->expects($this->once())->method('setText')->with('Parsed text');

		$reflection = new \ReflectionClass($this->handler);
		$method = $reflection->getMethod('buildAlertText');
		$method->setAccessible(true);
		$method->invoke($this->handler);
	}

	public function testBuildTargetHref(): void
	{
		$this->alertEntity->expects($this->once())->method('getSenderId')->willReturn(1);
		$this->handler->expects($this->once())->method('global')->willReturn('http://example.com');
		$this->handler->expects($this->once())->method('parserText')->with($this->stringContains('u={userId}'), $this->callback(function ($params) {
			return isset($params['userId']) && $params['userId'] === 1;
		}))->willReturn('http://example.com/u/1');
		$this->alertEntity->expects($this->once())->method('setTargetHref')->with('http://example.com/u/1');

		$reflection = new \ReflectionClass($this->handler);
		$method = $reflection->getMethod('buildTargetHref');
		$method->setAccessible(true);
		$method->invoke($this->handler);
	}
}
