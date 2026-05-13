<?php

declare(strict_types=1);

namespace Breeze\Event\Like;

use Breeze\Entity\AlertEntity;
use Breeze\Enums\LikesEnum;
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
		$senderName = 'Luffy';
		$contentType = LikesEnum::Status->value;
		$contentId = 123;
		$scriptUrl = 'http://example.com';
		$alertLikeText = 'mocked_alert_like_text';
		$statusTypeText = 'mocked_status_type_text';
		$parsedAlertText = 'Luffy liked your status';
		$parsedTargetHref = 'http://example.com?action=wall;id=123';


		$this->alertEntity->method('getSenderName')->willReturn($senderName);

		$this->alertEntity->expects($this->once())
			->method('getExtra')
			->willReturn(['content_type' => $contentType, 'content_id' => $contentId]);

		// getText is called twice: once for 'alert_like', once for 'alert_' . $contentType
		$this->handler->expects($this->exactly(2))
			->method('getText')
			->willReturnMap([
				['alert_like', $alertLikeText],
				['alert_' . $contentType, $statusTypeText],
			]);

		// parserText is called twice: once for buildAlertText, once for buildTargetHref
		$this->handler->expects($this->exactly(2))
			->method('parserText')
			->willReturnMap([
				[
					$alertLikeText,
					['poster' => $senderName, 'type' => $statusTypeText],
					$parsedAlertText,
				],
				[
					LikeCreatedHandler::TARGET_HREF,
					['scriptUrl' => $scriptUrl, 'action' => 'wall', 'contentId' => $contentId],
					$parsedTargetHref,
				],
			]);

		$this->handler->expects($this->once())
			->method('global')
			->willReturn($scriptUrl);

		$this->alertEntity->expects($this->once())
			->method('setText')
			->with($parsedAlertText);

		$this->alertEntity->expects($this->once())
			->method('setTargetHref')
			->with($parsedTargetHref);

		$this->alertEntity->expects($this->once())
			->method('toArray')
			->willReturn($expectedArray);

		$result = $this->handler->resolve();

		$this->assertIsArray($result);
		$this->assertEquals($expectedArray, $result);
	}

	public function testBuildAlertTextWithStatusType(): void
	{
		$senderName = 'Jane Doe';
		$contentType = LikesEnum::Status->value;
		$alertLikeText = 'alert_like_text';
		$statusTypeText = 'Status type text';
		$parsedAlertText = 'Jane Doe liked your status';

		$this->alertEntity->method('getSenderName')->willReturn($senderName);

		$reflection = new \ReflectionClass($this->handler);
		$extraProperty = $reflection->getProperty('extra');
		$extraProperty->setAccessible(true);
		$extraProperty->setValue($this->handler, ['content_type' => $contentType]);

		// getText is called twice: once for 'alert_like', once for 'alert_' . $contentType
		$this->handler->expects($this->exactly(2))
			->method('getText')
			->willReturnMap([
				['alert_like', $alertLikeText],
				['alert_' . $contentType, $statusTypeText],
			]);

		$this->handler->expects($this->once())
			->method('parserText')
			->with(
				$alertLikeText,
				$this->callback(function ($params) use ($senderName, $statusTypeText) {
					return $params['poster'] === $senderName && $params['type'] === $statusTypeText;
				})
			)
			->willReturn($parsedAlertText);

		$this->alertEntity->expects($this->once())
			->method('setText')
			->with($parsedAlertText);

		$method = $reflection->getMethod('buildAlertText');
		$method->setAccessible(true);
		$method->invoke($this->handler);
	}

	public function testBuildAlertTextWithCommentType(): void
	{
		$senderName = 'Bob Smith';
		$contentType = LikesEnum::Comments->value;
		$alertLikeText = 'alert_like_text';
		$commentTypeText = 'Comment type text';
		$parsedAlertText = 'Bob Smith liked your comment';

		$this->alertEntity->method('getSenderName')->willReturn($senderName);

		$reflection = new \ReflectionClass($this->handler);
		$extraProperty = $reflection->getProperty('extra');
		$extraProperty->setAccessible(true);
		$extraProperty->setValue($this->handler, ['content_type' => $contentType]);

		// getText is called twice: once for 'alert_like', once for 'alert_' . $contentType
		$this->handler->expects($this->exactly(2))
			->method('getText')
			->willReturnMap([
				['alert_like', $alertLikeText],
				['alert_' . $contentType, $commentTypeText],
			]);

		$this->handler->expects($this->once())
			->method('parserText')
			->with(
				$alertLikeText,
				$this->callback(function ($params) use ($senderName, $commentTypeText) {
					return $params['poster'] === $senderName && $params['type'] === $commentTypeText;
				})
			)
			->willReturn($parsedAlertText);

		$this->alertEntity->expects($this->once())
			->method('setText')
			->with($parsedAlertText);

		$method = $reflection->getMethod('buildAlertText');
		$method->setAccessible(true);
		$method->invoke($this->handler);
	}

	public function testBuildAlertTextWithInvalidContentType(): void
	{
		$this->alertEntity->method('getSenderName')->willReturn('Invalid User');

		$reflection = new \ReflectionClass($this->handler);
		$extraProperty = $reflection->getProperty('extra');
		$extraProperty->setAccessible(true);
		$extraProperty->setValue($this->handler, ['content_type' => 'invalid_type']);

		$this->handler->expects($this->never())
			->method('getText');

		$this->handler->expects($this->never())
			->method('parserText');

		$this->alertEntity->expects($this->never())
			->method('setText');

		$method = $reflection->getMethod('buildAlertText');
		$method->setAccessible(true);
		$method->invoke($this->handler);
	}

	public function testBuildTargetHrefWithStatusType(): void
	{
		$contentType = LikesEnum::Status->value;
		$contentId = 456;
		$scriptUrl = 'http://example.com';
		$parsedTargetHref = 'http://example.com?action=wall;id=456';

		$reflection = new \ReflectionClass($this->handler);
		$extraProperty = $reflection->getProperty('extra');
		$extraProperty->setAccessible(true);
		$extraProperty->setValue($this->handler, ['content_type' => $contentType, 'content_id' => $contentId]);

		$this->handler->expects($this->once())
			->method('global')
			->willReturn($scriptUrl);

		$this->handler->expects($this->once())
			->method('parserText')
			->with(
				LikeCreatedHandler::TARGET_HREF,
				$this->callback(function ($params) use ($scriptUrl, $contentId) {
					return $params['scriptUrl'] === $scriptUrl && $params['action'] === 'wall' && $params['contentId'] === $contentId;
				})
			)
			->willReturn($parsedTargetHref);

		$this->alertEntity->expects($this->once())
			->method('setTargetHref')
			->with($parsedTargetHref);

		$method = $reflection->getMethod('buildTargetHref');
		$method->setAccessible(true);
		$method->invoke($this->handler);
	}

	public function testBuildTargetHrefWithCommentType(): void
	{
		$contentType = LikesEnum::Comments->value;
		$contentId = 789;
		$scriptUrl = 'http://example.com';
		$parsedTargetHref = 'http://example.com?action=wall;id=789';

		$reflection = new \ReflectionClass($this->handler);
		$extraProperty = $reflection->getProperty('extra');
		$extraProperty->setAccessible(true);
		$extraProperty->setValue($this->handler, ['content_type' => $contentType, 'content_id' => $contentId]);

		$this->handler->expects($this->once())
			->method('global')
			->willReturn($scriptUrl);

		$this->handler->expects($this->once())
			->method('parserText')
			->with(
				LikeCreatedHandler::TARGET_HREF,
				$this->callback(function ($params) use ($scriptUrl, $contentId) {
					return $params['scriptUrl'] === $scriptUrl && $params['action'] === 'wall' && $params['contentId'] === $contentId;
				})
			)
			->willReturn($parsedTargetHref);

		$this->alertEntity->expects($this->once())
			->method('setTargetHref')
			->with($parsedTargetHref);

		$method = $reflection->getMethod('buildTargetHref');
		$method->setAccessible(true);
		$method->invoke($this->handler);
	}
}
