<?php

declare(strict_types=1);

namespace Breeze\Entity;

class StatusHandledEntity extends StatusEntity implements HandledEntityInterface
{
	protected array $usersInfo = [];

	protected array $likesInfo = [];

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

	public function getLikesInfo(): array
	{
		return $this->likesInfo;
	}

	public function setLikesInfo(LikeHandledEntity $likesInfo): void
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
}
