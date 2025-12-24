<?php

declare(strict_types=1);

namespace Breeze\Event;

use Breeze\Entity\AlertEntity;
use Breeze\Event\Comment\CommentCreatedHandler;
use Breeze\Event\Like\LikeCreatedHandler;
use Breeze\Event\Status\StatusCreatedHandler;
use Breeze\Util\Validate\DataNotFoundException;
use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\TestCase;
use ReflectionException;

#[AllowMockObjectsWithoutExpectations]
class HandlerServiceProviderTest extends TestCase
{
	private HandlerServiceProvider $handlerServiceProvider;

	protected function setUp(): void
	{
		$this->handlerServiceProvider = new HandlerServiceProvider();
	}

	/**
	 * @throws Exception
	 * @throws DataNotFoundException
	 */
	#[DataProvider('validHandlerProvider')]
	public function testGetHandlerReturnsCorrectHandler(string $contentType, string $contentAction, string $expectedClass): void
	{
		$alertEntity = $this->createStub(AlertEntity::class);
		$alertEntity->method('getContentType')->willReturn($contentType);
		$alertEntity->method('getContentAction')->willReturn($contentAction);

		$handler = $this->handlerServiceProvider->getHandler($alertEntity);

		$this->assertInstanceOf($expectedClass, $handler);
		$this->assertInstanceOf(EventHandlerInterface::class, $handler);
	}

	public static function validHandlerProvider(): array
	{
		return [
			'status created' => ['Breeze_status', 'Breeze_created', StatusCreatedHandler::class],
			'comment created' => ['Breeze_comment', 'Breeze_created', CommentCreatedHandler::class],
			'like created' => ['Breeze_like', 'Breeze_created', LikeCreatedHandler::class],
		];
	}

	/**
	 * @throws Exception
	 */
	#[DataProvider('invalidHandlerProvider')]
	public function testGetHandlerThrowsExceptionForInvalidHandler(string $contentType, string $contentAction): void
	{
		$alertEntity = $this->createStub(AlertEntity::class);
		$alertEntity->method('getContentType')->willReturn($contentType);
		$alertEntity->method('getContentAction')->willReturn($contentAction);

		$this->expectException(DataNotFoundException::class);
		$this->expectExceptionMessage('Handler not found');

		$this->handlerServiceProvider->getHandler($alertEntity);
	}

	public static function invalidHandlerProvider(): array
	{
		return [
			'invalid type' => ['breeze_invalid', 'breeze_created'],
			'invalid action' => ['breeze_status', 'breeze_invalid'],
			'both invalid' => ['invalid', 'invalid'],
			'empty type' => ['', 'breeze_created'],
			'empty action' => ['breeze_status', ''],
			'status deleted' => ['breeze_status', 'breeze_deleted'],
			'comment deleted' => ['breeze_comment', 'breeze_deleted'],
		];
	}

	/**
	 * @throws Exception
	 * @throws DataNotFoundException
	 */
	public function testGetHandlerPassesAlertEntityToHandler(): void
	{
		$alertEntity = $this->createStub(AlertEntity::class);
		$alertEntity->method('getContentType')->willReturn('Breeze_status');
		$alertEntity->method('getContentAction')->willReturn('Breeze_created');

		$handler = $this->handlerServiceProvider->getHandler($alertEntity);

		$this->assertInstanceOf(StatusCreatedHandler::class, $handler);
	}

	/**
	 * @throws ReflectionException
	 * @throws Exception
	 */
	public function testBuildHandlerNameWithStatusCreated(): void
	{
		$alertEntity = $this->createStub(AlertEntity::class);
		$alertEntity->method('getContentType')->willReturn('breeze_status');
		$alertEntity->method('getContentAction')->willReturn('breeze_created');

		$reflection = new \ReflectionClass($this->handlerServiceProvider);
		$method = $reflection->getMethod('buildHandlerName');
		$method->setAccessible(true);

		$result = $method->invoke($this->handlerServiceProvider, $alertEntity);

		$this->assertEquals('Breeze_statusBreeze_created', $result);
	}

	/**
	 * @throws ReflectionException
	 * @throws Exception
	 */
	public function testBuildHandlerNameWithCommentCreated(): void
	{
		$alertEntity = $this->createStub(AlertEntity::class);
		$alertEntity->method('getContentType')->willReturn('Breeze_comment');
		$alertEntity->method('getContentAction')->willReturn('Breeze_created');

		$reflection = new \ReflectionClass($this->handlerServiceProvider);
		$method = $reflection->getMethod('buildHandlerName');
		$method->setAccessible(true);

		$result = $method->invoke($this->handlerServiceProvider, $alertEntity);

		$this->assertEquals('CommentCreated', $result);
	}

	/**
	 * @throws ReflectionException
	 * @throws Exception
	 */
	public function testBuildHandlerNameWithLikeCreated(): void
	{
		$alertEntity = $this->createStub(AlertEntity::class);
		$alertEntity->method('getContentType')->willReturn('Breeze_like');
		$alertEntity->method('getContentAction')->willReturn('Breeze_created');

		$reflection = new \ReflectionClass($this->handlerServiceProvider);
		$method = $reflection->getMethod('buildHandlerName');
		$method->setAccessible(true);

		$result = $method->invoke($this->handlerServiceProvider, $alertEntity);

		$this->assertEquals('LikeCreated', $result);
	}

	/**
	 * @throws ReflectionException
	 * @throws Exception
	 */
	public function testGetHandlerClassReturnsCorrectClass(): void
	{
		$alertEntity = $this->createStub(AlertEntity::class);
		$alertEntity->method('getContentType')->willReturn('Breeze_status');
		$alertEntity->method('getContentAction')->willReturn('Breeze_created');

		$reflection = new \ReflectionClass($this->handlerServiceProvider);
		$method = $reflection->getMethod('getHandlerClass');
		$method->setAccessible(true);

		$result = $method->invoke($this->handlerServiceProvider, $alertEntity);

		$this->assertEquals(StatusCreatedHandler::class, $result);
	}
}
