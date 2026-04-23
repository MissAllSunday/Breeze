<?php

declare(strict_types=1);

namespace Breeze\Event\Like;

use Breeze\Breeze;
use Breeze\Entity\AlertEntity;
use Breeze\Event\EventHandlerInterface;
use Breeze\Repository\AlertRepository;
use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

#[AllowMockObjectsWithoutExpectations]
class LikeCreatedHandlerTest extends TestCase
{
	private AlertEntity|MockObject $alertEntity;

	private AlertRepository|MockObject $alertRepository;

	private LikeCreatedHandler|MockObject $handler;

	/**
	 * @throws Exception
	 */
	protected function setUp(): void
	{
		$this->alertEntity = $this->createMock(AlertEntity::class);
		$this->alertRepository = $this->createMock(AlertRepository::class);

		$this->handler = $this->getMockBuilder(LikeCreatedHandler::class)
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

		$this->alertEntity->expects($this->exactly(2))
			->method('getExtra')
			->willReturn(['content_type' => Breeze::NAME . '_status', 'content_id' => 123]);

		$this->alertEntity->method('getSenderName')->willReturn('Luffy');

		$this->handler->expects($this->atLeast(1))
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
			->method('toArray')
			->willReturn($expectedArray);

		$result = $this->handler->resolve();

		$this->assertIsArray($result);
		$this->assertEquals($expectedArray, $result);
	}

	public function testBuildAlertTextWithStatusType(): void
	{
		$extra = ['content_type' => Breeze::NAME . '_status'];

		$this->alertEntity->expects($this->once())
			->method('getExtra')
			->willReturn($extra);

		$this->alertEntity->method('getSenderName')->willReturn('Jane Doe');

		$this->handler->expects($this->exactly(2))
			->method('getText')
			->willReturnOnConsecutiveCalls('general.status', 'alert_like');

		$this->handler->expects($this->once())
			->method('parserText')
			->willReturn('Jane Doe liked your status');

		$this->alertEntity->expects($this->once())
			->method('setText')
			->with('Jane Doe liked your status');

		$reflection = new \ReflectionClass($this->handler);
		$method = $reflection->getMethod('buildAlertText');
		$method->setAccessible(true);
		$method->invoke($this->handler);
	}

	public function testBuildAlertTextWithCommentType(): void
	{
		$extra = ['content_type' => Breeze::NAME . '_comment'];

		$this->alertEntity->expects($this->once())
			->method('getExtra')
			->willReturn($extra);

		$this->alertEntity->method('getSenderName')->willReturn('Bob Smith');

		$this->handler->expects($this->exactly(2))
			->method('getText')
			->willReturnOnConsecutiveCalls('general.comment', 'alert_like');

		$this->handler->expects($this->once())
			->method('parserText')
			->willReturn('Bob Smith liked your comment');

		$this->alertEntity->expects($this->once())
			->method('setText')
			->with('Bob Smith liked your comment');

		$reflection = new \ReflectionClass($this->handler);
		$method = $reflection->getMethod('buildAlertText');
		$method->setAccessible(true);
		$method->invoke($this->handler);
	}

	public function testBuildTargetHrefWithStatusType(): void
	{
		$extra = ['content_type' => Breeze::NAME . '_status', 'content_id' => 456];

		$this->alertEntity->expects($this->once())
			->method('getExtra')
			->willReturn($extra);

		$this->handler->expects($this->once())
			->method('global')
			->willReturn('http://example.com');

		$this->handler->expects($this->once())
			->method('parserText')
			->with($this->anything(), $this->callback(function ($params) {
				return isset($params['contentId']) && $params['contentId'] === 456;
			}))
			->willReturn('http://example.com?action=wall;sa=single;id=456');

		$this->alertEntity->expects($this->once())
			->method('setTargetHref')
			->with('http://example.com?action=wall;sa=single;id=456');

		$reflection = new \ReflectionClass($this->handler);
		$method = $reflection->getMethod('buildTargetHref');
		$method->setAccessible(true);
		$method->invoke($this->handler);
	}

	public function testBuildTargetHrefWithCommentType(): void
	{
		$extra = ['content_type' => Breeze::NAME . '_comment', 'content_id' => 789];

		$this->alertEntity->expects($this->once())
			->method('getExtra')
			->willReturn($extra);

		$this->handler->expects($this->once())
			->method('global')
			->willReturn('http://example.com');

		$this->handler->expects($this->once())
			->method('parserText')
			->willReturn('http://example.com?action=wall;sa=single;id=789');

		$this->alertEntity->expects($this->once())
			->method('setTargetHref');

		$reflection = new \ReflectionClass($this->handler);
		$method = $reflection->getMethod('buildTargetHref');
		$method->setAccessible(true);
		$method->invoke($this->handler);
	}
}
