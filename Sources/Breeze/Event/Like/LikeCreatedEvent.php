<?php

declare(strict_types=1);

namespace Breeze\Event\Like;

use Breeze\Event\EventAbstract;

class LikeCreatedEvent extends EventAbstract
{
	public function __construct(
		protected int $likeId,
		protected int $userId,
		protected int $contentId,
		protected string $contentType,
		protected int $contentOwnerId
	) {
	}

	public function eventName(): string
	{
		return self::class;
	}

	public function getLikeId(): int
	{
		return $this->likeId;
	}

	public function getUserId(): int
	{
		return $this->userId;
	}

	public function getContentId(): int
	{
		return $this->contentId;
	}

	public function getContentType(): string
	{
		return $this->contentType;
	}

	public function getContentOwnerId(): int
	{
		return $this->contentOwnerId;
	}
}
