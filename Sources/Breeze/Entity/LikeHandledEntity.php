<?php

declare(strict_types=1);


namespace Breeze\Entity;

class LikeHandledEntity extends LikeEntity implements \JsonSerializable
{
	protected int $count = 0;

	protected bool $already_liked = false;

	protected bool $can_like = false;

	protected array $additional_info = [];

	public function getCount(): int
	{
		return $this->count;
	}

	public function setCount(int $count): void
	{
		$this->count = $count;
	}

	public function isAlreadyLiked(): bool
	{
		return $this->already_liked;
	}

	public function setAlreadyLiked(bool $already_liked): void
	{
		$this->already_liked = $already_liked;
	}

	public function canLike(): bool
	{
		return $this->can_like;
	}

	public function setCanLike(bool $can_like): void
	{
		$this->can_like = $can_like;
	}

	public function getAdditionalInfo(): array
	{
		return $this->additional_info;
	}

	public function setAdditionalInfo(array $additional_info): void
	{
		$this->additional_info = $additional_info;
	}

	public function jsonSerialize(): array
	{
		return [
			'contentId' => $this->getContentId(),
			'count' => $this->getCount(),
			'alreadyLiked' => $this->isAlreadyLiked(),
			'canLike' => $this->canLike(),
			'type' => $this->getContentType(),
			'additionalInfo' => $this->getAdditionalInfo(),
		];
	}
}
