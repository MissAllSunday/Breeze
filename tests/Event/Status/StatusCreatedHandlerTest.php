<?php

declare(strict_types=1);

namespace Breeze\Event\Status;

use Breeze\Entity\AlertEntity;
use Breeze\Event\EventHandlerInterface;
use Breeze\Repository\AlertRepository;
use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

#[AllowMockObjectsWithoutExpectations]
class StatusCreatedHandlerTest extends TestCase
{
	private AlertEntity|MockObject $alertEntity;

	private AlertRepository|MockObject $alertRepository;

	private StatusCreatedHandler|MockObject $handler;

	/**
	 * @throws Exception
	 */
	protected function setUp(): void
	{
		$this->alertEntity = $this->createMock(AlertEntity::class);
		$this->alertRepository = $this->createMock(AlertRepository::class);

		$this->handler = $this->getMockBuilder(StatusCreatedHandler::class)
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

		$this->alertEntity->method('getSenderName')->willReturn('John Doe');
		$this->alertEntity->method('getContentId')->willReturn(123);

		$this->handler->expects($this->once())
			->method('getText')
			->willReturn('Alert text');

		$this->handler->expects($this->exactly(2))
			->method('parserText')
			->willReturn('Parsed text');

		$this->handler->expects($this->once())
			->method('global')
			->willReturn('http://example.com');

		$this->alertEntity->expects($this->once())
			->method('setText');

		$this->alertEntity->expects($this->once())
			->method('setTargetHref');

		$this->alertEntity->expects($this->once())
			->method('setIcon')
			->with('<span class="alert_icon main_icons people"></span>');

		$this->alertEntity->expects($this->once())
			->method('toArray')
			->willReturn($expectedArray);

		$result = $this->handler->resolve();

		$this->assertIsArray($result);
		$this->assertEquals($expectedArray, $result);
	}

	public function testResolveCallsBuildAlertText(): void
	{
		$this->alertEntity->method('getSenderName')->willReturn('Jane Doe');
		$this->alertEntity->method('getContentId')->willReturn(456);
		$this->alertEntity->method('toArray')->willReturn([]);
		$this->handler->method('getText')->willReturn('text');
		$this->handler->method('parserText')->willReturn('parsed');
		$this->handler->method('global')->willReturn('url');

		$this->alertEntity->expects($this->once())
			->method('setText');

		$this->handler->resolve();
	}

	public function testResolveCallsBuildTargetHref(): void
	{
		$this->alertEntity->method('getSenderName')->willReturn('Bob Smith');
		$this->alertEntity->method('getContentId')->willReturn(789);
		$this->alertEntity->method('toArray')->willReturn([]);
		$this->handler->method('getText')->willReturn('text');
		$this->handler->method('parserText')->willReturn('parsed');
		$this->handler->method('global')->willReturn('url');

		$this->alertEntity->expects($this->once())
			->method('setTargetHref');

		$this->handler->resolve();
	}

	public function testResolveSetsIcon(): void
	{
		$this->alertEntity->method('getSenderName')->willReturn('Alice');
		$this->alertEntity->method('getContentId')->willReturn(111);
		$this->alertEntity->method('toArray')->willReturn([]);
		$this->handler->method('getText')->willReturn('text');
		$this->handler->method('parserText')->willReturn('parsed');
		$this->handler->method('global')->willReturn('url');

		$this->alertEntity->expects($this->once())
			->method('setIcon')
			->with('<span class="alert_icon main_icons people"></span>');

		$this->handler->resolve();
	}

	public function testBuildAlertTextUsesSenderName(): void
	{
		$this->alertEntity->expects($this->once())
			->method('getSenderName')
			->willReturn('Test User');

		$this->handler->expects($this->once())
			->method('getText')
			->willReturn('alert_status_owner');

		$this->handler->expects($this->once())
			->method('parserText')
			->with('alert_status_owner', $this->callback(function ($params) {
				return isset($params['poster']) && $params['poster'] === 'Test User';
			}))
			->willReturn('Test User posted on your wall');

		$this->alertEntity->expects($this->once())
			->method('setText')
			->with('Test User posted on your wall');

		$reflection = new \ReflectionClass($this->handler);
		$method = $reflection->getMethod('buildAlertText');
		$method->setAccessible(true);
		$method->invoke($this->handler);
	}

	public function testBuildTargetHrefUsesContentId(): void
	{
		$reflection = new \ReflectionClass($this->handler);

		$extraProperty = $reflection->getProperty('extra');
		$extraProperty->setAccessible(true);
		$extraProperty->setValue($this->handler, ['wall_id' => 2, 'status_id' => 999]);

		$this->handler->expects($this->once())
			->method('global')
			->willReturn('http://example.com');

		$this->handler->expects($this->once())
			->method('parserText')
			->with($this->anything(), $this->callback(function ($params) {
				return isset($params['statusId']) && $params['statusId'] === 999
					&& isset($params['wallOwnerId']) && $params['wallOwnerId'] === 2;
			}))
			->willReturn('http://example.com?action=profile;area=summary;u=2#status-999');

		$this->alertEntity->expects($this->once())
			->method('setTargetHref')
			->with('http://example.com?action=profile;area=summary;u=2#status-999');

		$method = $reflection->getMethod('buildTargetHref');
		$method->setAccessible(true);
		$method->invoke($this->handler);
	}
}
