<?php

declare(strict_types=1);

namespace Breeze\Event\Comment;

use Breeze\Entity\CommentEntity;
use League\Event\HasEventName;

class CommentDeletedEvent implements HasEventName
{
	public function __construct(
		protected int $commentId,
		protected int $userId,
		protected int $statusId,
		protected int $statusOwnerId,
		protected int $wallId
	) {
	}

	public function eventName(): string
	{
		return self::class;
	}

	public function getCommentId(): int
	{
		return $this->commentId;
	}

	public function getUserId(): int
	{
		return $this->userId;
	}

	public function getStatusId(): int
	{
		return $this->statusId;
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
