<?php

declare(strict_types=1);

namespace Breeze\Event\Comment;

use Breeze\Entity\CommentEntity;
use Breeze\Entity\StatusEntity;
use Breeze\Event\EventAbstract;

class CommentCreatedEvent extends EventAbstract
{
	public function __construct(
		protected CommentEntity $commentEntity,
		protected StatusEntity $statusEntity
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
		return $this->statusEntity->getUserId();
	}

	public function getWallId(): int
	{
		return $this->statusEntity->getWallId();
	}

	public function getStatusEntity(): StatusEntity
	{
		return $this->statusEntity;
	}
}
