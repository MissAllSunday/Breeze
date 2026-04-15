<?php

declare(strict_types=1);

namespace Breeze\Event\Comment;

use Breeze\Entity\AlertEntity;
use Breeze\Event\EventAbstract;
use Breeze\Event\EventHandlerInterface;
use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

#[AllowMockObjectsWithoutExpectations]
class CommentDeletedHandlerTest extends TestCase
{
	private AlertEntity|MockObject $alertEntity;

	private CommentDeletedHandler|MockObject $handler;

	/**
	 * @throws Exception
	 */
	protected function setUp(): void
	{
		$this->alertEntity = $this->createMock(AlertEntity::class);

		$this->handler = $this->getMockBuilder(CommentDeletedHandler::class)
			->setConstructorArgs([$this->alertEntity])
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

		$this->alertEntity->expects($this->once())
			->method('getContentAction')
			->willReturn('default');

		$this->alertEntity->expects($this->once())
			->method('getExtra')
			->willReturn(['status_id' => 123]);

		$this->handler->expects($this->once())
			->method('global')
			->willReturn('http://example.com');

		$this->handler->expects($this->once())
			->method('parserText')
			->willReturn('http://example.com?action=wall;sa=single;id=123');

		$this->alertEntity->expects($this->once())
			->method('setTargetHref');

		$this->alertEntity->expects($this->once())
			->method('toArray')
			->willReturn($expectedArray);

		$result = $this->handler->resolve();

		$this->assertIsArray($result);
		$this->assertEquals($expectedArray, $result);
	}

	public function testBuildAlertTextWithWallOwnerAction(): void
	{
		$contentAction = EventAbstract::CONTENT_ACTION_DELETED . EventAbstract::WALL_OWNER;

		$this->alertEntity->expects($this->once())
			->method('getContentAction')
			->willReturn($contentAction);

		$this->alertEntity->method('getSenderName')->willReturn('John Doe');
		$this->handler->method('getText')->willReturn('Alert text');
		$this->handler->method('parserText')->willReturn('Parsed alert text');

		$this->alertEntity->expects($this->once())
			->method('setText');

		$reflection = new \ReflectionClass($this->handler);
		$method = $reflection->getMethod('buildAlertText');
		$method->setAccessible(true);
		$method->invoke($this->handler);
	}

	public function testBuildAlertTextWithStatusOwnerAction(): void
	{
		$contentAction = EventAbstract::CONTENT_ACTION_DELETED . EventAbstract::STATUS_OWNER;

		$this->alertEntity->expects($this->once())
			->method('getContentAction')
			->willReturn($contentAction);

		$this->alertEntity->method('getSenderName')->willReturn('Jane Doe');
		$this->handler->method('getText')->willReturn('Alert text');
		$this->handler->method('parserText')->willReturn('Parsed alert text');

		$this->alertEntity->expects($this->once())
			->method('setText');

		$reflection = new \ReflectionClass($this->handler);
		$method = $reflection->getMethod('buildAlertText');
		$method->setAccessible(true);
		$method->invoke($this->handler);
	}

	public function testBuildTargetHrefWithStatusId(): void
	{
		$extra = ['status_id' => 456];

		$this->alertEntity->expects($this->once())
			->method('getExtra')
			->willReturn($extra);

		$this->handler->expects($this->once())
			->method('global')
			->willReturn('http://example.com');

		$this->handler->expects($this->once())
			->method('parserText')
			->with($this->anything(), $this->callback(function ($params) {
				return isset($params['statusId']) && $params['statusId'] === 456;
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
}
