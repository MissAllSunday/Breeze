<?php

declare(strict_types=1);

namespace Breeze\Entity;

use Breeze\Util\Time;

class StatusHandledEntity extends StatusEntity implements HandledEntityInterface
{
	protected array $usersInfo = [];

	protected ?LikeHandledEntity $likesInfo = null;

	/** @var CommentHandledEntity[] */
	protected array $comments = [];

	protected bool $isNew = false;

	public function getUsersInfo(): array
	{
		return $this->usersInfo;
	}

	public function setUsersInfo(array $usersInfo): void
	{
		$this->usersInfo = $usersInfo;
	}

	public function getLikesInfo(): ?LikeHandledEntity
	{
		return $this->likesInfo;
	}

	public function setLikesInfo(?LikeHandledEntity $likesInfo): void
	{
		$this->likesInfo = $likesInfo;
	}

	public function getComments(): array
	{
		return $this->comments;
	}

	public function setComments(array $comments): void
	{
		$this->comments = $comments;
	}

	public function isNew(): bool
	{
		return $this->isNew;
	}

	public function setIsNew(bool $isNew): void
	{
		$this->isNew = $isNew;
	}

	/**
	 * @throws \DateMalformedStringException
	 */
	public function jsonSerialize(): array
	{
		return [
			'id' => $this->getId(),
			'wallId' => $this->getWallId(),
			'userId' => $this->getUserId(),
			'likes' => 0,  // @deprecated use likesInfo.count instead
			'body' => $this->getBody(),
			'createdAt' => Time::from($this->getCreatedAt()),
			'likesInfo' => $this->getLikesInfo(),
			'comments' => $this->getComments(),
			'userData' => $this->getUsersInfo()[$this->getUserId()] ?? [],
			'isNew' => $this->isNew(),
		];
	}
}
