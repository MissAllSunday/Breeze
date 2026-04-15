<?php

declare(strict_types=1);

namespace Breeze\Event;

use Breeze\Breeze;
use Breeze\Entity\AlertEntity;
use Breeze\Event\Buddy\BuddyAcceptedHandler;
use Breeze\Event\Buddy\BuddyInviteHandler;
use Breeze\Event\Comment\CommentCreatedHandler;
use Breeze\Event\Comment\CommentDeletedHandler;
use Breeze\Event\Like\LikeCreatedHandler;
use Breeze\Event\Status\StatusCreatedHandler;
use Breeze\Util\Validate\DataNotFoundException;

class HandlerServiceProvider
{
	/**
	 * @throws DataNotFoundException
	 */
	public function getHandler(AlertEntity $alertEntity): EventHandlerInterface
	{
		$handlerClass = $this->getHandlerClass($alertEntity);

		return new $handlerClass($alertEntity);
	}

	private const array OWNER_SUFFIXES = [
		EventAbstract::STATUS_OWNER,
		EventAbstract::WALL_OWNER,
		EventAbstract::COMMENT_OWNER,
		EventAbstract::OWNER,
	];

	protected function buildHandlerName(AlertEntity $alertEntity): string
	{
		$type = ucfirst(str_replace(Breeze::PATTERN, '', $alertEntity->getContentType()));
		$action = str_replace(Breeze::PATTERN, '', $alertEntity->getContentAction());

		foreach (self::OWNER_SUFFIXES as $suffix) {
			if (str_ends_with($action, $suffix)) {
				$action = substr($action, 0, -strlen($suffix));

				break;
			}
		}

		return $type . ucfirst($action);
	}

	/**
	 * @throws DataNotFoundException
	 */
	protected function getHandlerClass(AlertEntity $alertEntity): string
	{
		return match ($this->buildHandlerName($alertEntity)) {
			'StatusCreated' => StatusCreatedHandler::class,
			'CommentCreated' => CommentCreatedHandler::class,
			'CommentDeleted' => CommentDeletedHandler::class,
			'LikeCreated' => LikeCreatedHandler::class,
			'BuddyInvite' => BuddyInviteHandler::class,
			'BuddyAccepted' => BuddyAcceptedHandler::class,
			default => throw new DataNotFoundException('Handler not found'),
		};
	}
}
