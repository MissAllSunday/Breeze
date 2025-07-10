<?php

declare(strict_types=1);

namespace Breeze\Entity;

use DateMalformedStringException;
use DateTimeInterface;

class CommentHandledEntity extends CommentEntity implements HandledEntityInterface
{
	protected array $usersInfo = [];

	protected ?LikeHandledEntity $likesInfo = null;

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

	public function getWallId(): int
	{
		return 0;
	}

	/**
	 * @throws DateMalformedStringException
	 */
	public function jsonSerialize(): array
	{
		return [
			'id' => $this->getId(),
			'statusId' => $this->getStatusId(),
			'userId' => $this->getUserId(),
			'createdAt' => $this->getCreatedAt()->format(DateTimeInterface::ATOM),
			'body' => $this->getBody(),
			'likes' => 0,  // @deprecated use likesInfo.count instead
			'likesInfo' => $this->getLikesInfo(),
			'userData' => $this->getUsersInfo()[$this->getUserId()] ?? [],
		];
	}
}
