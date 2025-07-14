<?php

declare(strict_types=1);

namespace Breeze\Event;

use Breeze\Breeze;
use Breeze\Entity\AlertEntity;
use Breeze\Event\Comment\CommentCreatedHandler;
use Breeze\Event\Like\LikeCreatedHandler;
use Breeze\Event\Status\StatusCreatedHandler;
use Breeze\Util\Validate\DataNotFoundException;

class HandlerServiceProvider
{
	public function __construct(
	) {}

	/**
	 * @throws DataNotFoundException
	 */
	public function getHandler(AlertEntity $alertEntity): EventHandlerInterface
	{
		$handlerClass = $this->getHandlerClass($alertEntity);

		return new $handlerClass($alertEntity);
	}

	protected function buildHandlerName(AlertEntity $alertEntity): string
	{
		return ucfirst(str_replace(Breeze::PATTERN, '', $alertEntity->getContentType())) .
			ucfirst(str_replace(Breeze::PATTERN, '', $alertEntity->getContentAction()));
	}

	/**
	 * @throws DataNotFoundException
	 */
	protected function getHandlerClass(AlertEntity $alertEntity): string
	{
		return match ($this->buildHandlerName($alertEntity)) {
			'StatusCreated' => StatusCreatedHandler::class,
			'CommentCreated' => CommentCreatedHandler::class,
			'LikeCreated' => LikeCreatedHandler::class,
			default => throw new DataNotFoundException('Handler not found'),
		};
	}
}
