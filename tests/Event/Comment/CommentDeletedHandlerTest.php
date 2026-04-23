<?php

declare(strict_types=1);

namespace Breeze\Event\Comment;

use Breeze\Entity\AlertEntity;
use Breeze\Event\EventAbstract;
use Breeze\Event\EventHandlerInterface;
use Breeze\Repository\AlertRepository;
use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

#[AllowMockObjectsWithoutExpectations]
class CommentDeletedHandlerTest extends TestCase
{
	private AlertEntity|MockObject $alertEntity;

	private AlertRepository|MockObject $alertRepository;

	private CommentDeletedHandler|MockObject $handler;

	/**
	 * @throws Exception
	 */
	protected function setUp(): void
	{
		$this->alertEntity = $this->createMock(AlertEntity::class);
		$this->alertRepository = $this->createMock(AlertRepository::class);

		$this->handler = $this->getMockBuilder(CommentDeletedHandler::class)
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

		$this->alertRepository->method('loadUsersInfo')
			->willReturn([
				3 => ['name' => 'Status Owner'],
				5 => ['name' => 'Wall Owner'],
			]);

		$this->alertEntity->expects($this->once())
			->method('setText');

		$reflection = new \ReflectionClass($this->handler);

		$extraProperty = $reflection->getProperty('extra');
		$extraProperty->setAccessible(true);
		$extraProperty->setValue($this->handler, [
			'status_owner_id' => 3,
			'wall_id' => 5,
		]);

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

		$this->alertRepository->method('loadUsersInfo')
			->willReturn([
				5 => ['name' => 'Wall Owner'],
			]);

		$this->alertEntity->expects($this->once())
			->method('setText');

		$reflection = new \ReflectionClass($this->handler);

		$extraProperty = $reflection->getProperty('extra');
		$extraProperty->setAccessible(true);
		$extraProperty->setValue($this->handler, [
			'wall_id' => 5,
		]);

		$method = $reflection->getMethod('buildAlertText');
		$method->setAccessible(true);
		$method->invoke($this->handler);
	}

	public function testBuildTargetHrefWithStatusId(): void
	{
		$reflection = new \ReflectionClass($this->handler);

		$extraProperty = $reflection->getProperty('extra');
		$extraProperty->setAccessible(true);
		$extraProperty->setValue($this->handler, ['status_id' => 456]);

		$this->handler->expects($this->once())
			->method('global')
			->willReturn('http://example.com');

		$this->handler->expects($this->once())
			->method('parserText')
			->with($this->anything(), $this->callback(function ($params) {
				return isset($params['statusId']) && $params['statusId'] === 456
					&& isset($params['anchor']) && $params['anchor'] === '';
			}))
			->willReturn('http://example.com?action=wall;sa=single;id=456');

		$this->alertEntity->expects($this->once())
			->method('setTargetHref')
			->with('http://example.com?action=wall;sa=single;id=456');

		$method = $reflection->getMethod('buildTargetHref');
		$method->setAccessible(true);
		$method->invoke($this->handler);
	}
}
