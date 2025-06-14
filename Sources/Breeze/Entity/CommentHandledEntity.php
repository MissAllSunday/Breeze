<?php

declare(strict_types=1);

namespace Breeze\Entity;

class CommentHandledEntity extends CommentEntity implements HandledEntityInterface
{
	protected array $usersInfo = [];

	protected array $likesInfo = [];

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
}
