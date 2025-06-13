<?php

declare(strict_types=1);

namespace Breeze\Event\Comment;

use League\Event\HasEventName;

class CommentDeletedEvent implements HasEventName
{
	public function __construct(
		protected int $commentId,
		protected int $userId
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
}
