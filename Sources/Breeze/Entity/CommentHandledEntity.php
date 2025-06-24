<?php

declare(strict_types=1);

namespace Breeze\Entity;

use DateTimeInterface;

class CommentHandledEntity extends CommentEntity implements HandledEntityInterface
{
	protected array $usersInfo = [];

	/** @var LikeHandledEntity[] */
	protected array $likesInfo;

	public function getUsersInfo(): array
	{
		return $this->usersInfo;
	}

	public function setUsersInfo(array $usersInfo): void
	{
		$this->usersInfo = $usersInfo;
	}

	/**
	 * @return array [LikeHandledEntity]
	 */
	public function getLikesInfo(): array
	{
		return $this->likesInfo;
	}

	/**
	 * @param array $likesInfo [LikeHandledEntity]
	 */
	public function setLikesInfo(array $likesInfo): void
	{
		$this->likesInfo = $likesInfo;
	}

	public function getWallId(): int
	{
		return 0;
	}

	/**
	 * @throws \DateMalformedStringException
	 */
	public function jsonSerialize(): array
	{
		return [
			'id' => $this->getId(),
			'statusId' => $this->getStatusId(),
			'userId' => $this->getUserId(),
			'createdAt' => $this->getCreatedAt()->format(DateTimeInterface::ATOM),
			'body' => $this->getBody(),
			'likes' => $this->getLikesInfo()->getCount(),
			'likesInfo' => $this->getLikesInfo(),
			'userData' => $this->getUsersInfo(),
		];
	}
}
