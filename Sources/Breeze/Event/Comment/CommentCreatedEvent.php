<?php

declare(strict_types=1);

namespace Breeze\Event\Comment;

use Breeze\Entity\CommentEntity;
use Breeze\Event\EventAbstract;

class CommentCreatedEvent extends EventAbstract
{
	public function __construct(
		protected CommentEntity $commentEntity,
		protected int $statusOwnerId,
		protected int $wallId
	) {
	}

	public function eventName(): string
	{
		return self::class;
	}

	public function getCommentEntity(): CommentEntity
	{
		return $this->commentEntity;
	}

	public function getStatusOwnerId(): int
	{
		return $this->statusOwnerId;
	}

	public function getWallId(): int
	{
		return $this->wallId;
	}
}
