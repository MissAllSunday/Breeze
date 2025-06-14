<?php

declare(strict_types=1);

namespace Breeze\Entity;

interface HandledEntityInterface
{
	public function getUsersInfo(): array;

	public function setUsersInfo(array $usersInfo): void;

	/**
	 * @param $likesInfo LikeHandledEntity
	 */
	public function setLikesInfo(LikeHandledEntity $likesInfo): void;

	/**
	 * @return array [LikeHandledEntity]
	 */
	public function getLikesInfo(): array;

	public function getUserId(): int;

	public function getId(): int;
}
